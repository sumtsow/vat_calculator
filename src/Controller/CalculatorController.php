<?php

namespace App\Controller;

use App\Entity\Calculation;
use App\Entity\CountryRate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;


class CalculatorController extends AbstractController
{
	private $entity_manager;
	
	private $serializer;
	
	private $sent_type;

	public function __construct(EntityManagerInterface $entity_manager, SerializerInterface $serializer) {
		$this->entity_manager = $entity_manager;
		$this->serializer = $serializer;
		$this->sent_type = 'country';
	}

	/**
	 * @Route("/", name="app_calculator")
	 */
	public function index (Request $request): Response {
		$entity_manager = $this->entity_manager;
		$calculation = new Calculation();
		$form = $this->getForm($calculation, $entity_manager);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$entity_manager->persist($calculation);
			$entity_manager->flush();
			return $this->renderForm('calculator/result.html.twig', [
				'calculations' => $this->getHistory(),
				'results' => $this->serializer->normalize($data, null)
			]);
		}
		if ($request->request->get('action') == 'delete') {
			return $this->renderForm('calculator/index.html.twig', [
				'count' => $this->calculationsHistory('delete'),
				'form' => $form,
				'calculations' => $this->getHistory(),
			]);
		}
		return $this->renderForm('calculator/index.html.twig', [
			'form' => $form,
			'calculations' => $this->getHistory()
		]);
	}
	
	private function getHistory() {
		$calculations = $this->calculationsHistory('show');
		$calculations = $this->serializer->normalize($calculations, null);
		return $calculations;
	}
	
	/**
	 * @Route("/download", name="app_export")
	 * Function for exporting CSV files of calculations history
	 */
	public function exportCsv() {
		$calculations = $this->getHistory();
		$response = new StreamedResponse();
		$response->setCallback(function() use ($calculations) {
			$handle = fopen('php://output', 'w+');
			fputcsv($handle, [
				'Type of VAT',
				'Calculation based on',
				'VAT Rate',
				'VAT Operation',
				'Net Amount',
				'VAT Amount',
				'Gross Amount'
			],';');
			foreach ($calculations as $calculation) {
				$has_country = !empty($calculation['countryRate']) && !empty($calculation['countryRate']['countryName']);
				$currency = $has_country && !empty($calculation['countryRate']['currency']) && !empty($calculation['countryRate']['currency']['symbol']) ? $calculation['countryRate']['currency']['symbol'] : '';
				$row = [
					$has_country ? ' standard VAT' : 'Custom VAT',
					$currency.$calculation['basedOn'],
					$calculation['vatRate'].'%',
					$calculation['vatAdded'] ? 'VAT Added' : 'VAT Removed',
					$currency.$calculation['netAmount'],
					$currency.$calculation['vatAmount'],
					$currency.$calculation['grossAmount'],
				];
				fputcsv($handle, $row, ';');
			}
			
			fclose($handle);
		});
		$response->setStatusCode(200);
		$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
		$response->headers->set('Content-Disposition','attachment;');
		return $response;
	}

	/**
	 * Function for actions under calculations (show/delete)
	*/
	private function calculationsHistory ($action) {
		$entity_manager = $this->entity_manager;
		$db = $entity_manager->createQueryBuilder();
		switch ($action):
		case 'delete':
			$rows = 0;
			$db->update('App\Entity\Calculation', 'c')
				->set('c.deleted', 1)
				->where('c.deleted = 0');
			break;
		default:
		case 'show':
			$rows = [];
			$db->select('c, cr', 'curr')
				->from('App\Entity\Calculation', 'c')
				->leftJoin('c.country_rate', 'cr')
				->leftJoin('cr.currency', 'curr')
				->where('c.deleted = 0');
			break;
		endswitch;
		$rows = $db->getQuery()->execute();
		$entity_manager->flush();
		return $rows;
	}
	
	/**
	 * Function for form building
	 */
	private function getForm (Calculation $calculation, EntityManagerInterface $entity_manager) {
		$countries = $entity_manager->getRepository(CountryRate::class)->findBy([], ['country_name' => 'ASC']);
		$num_pattern = '[+-]?([0-9]*[.])?[0-9]+';
		$form = $this->createFormBuilder($calculation, [
				'attr' => [
					'id' => 'calculator_form'
				]
			])
			->add('country_rate', ChoiceType::class, [
				'choices'=> $countries,
				'choice_label' => function(?CountryRate $cr) {
					return $cr ? $cr->getCountryName() : '';
				},
				'choice_value' => function(?CountryRate $cr) {
					return $cr ? $cr->getId() : '';
				},
				'choice_attr' => function(?CountryRate $cr) {
					return $cr ? [
						'data-code' => $cr->getCountryCode(),
						'data-rate' => $cr->getRate(),
						'data-currency' => $cr->getCurrency()->getSymbol()
					] : [];
				},
				'required' => true,
				'placeholder' => '—',
				'label' => 'Choose country'
			])
			->add('vat_rate', TextType::class, [
				'required' => false,
				'attr' => [
					'readonly' => true,
					'pattern' => $num_pattern
				],
				'label' => 'VAT Rate, %'
			])
			->add('based_on', TextType::class, [
				'label' => 'Starting value',
				'attr' => [
					'pattern' => $num_pattern
				],
			])
			->add('net_amount', HiddenType::class)
			->add('vat_amount', HiddenType::class)
			->add('gross_amount', HiddenType::class)
			->add('deleted', HiddenType::class, [
				'empty_data' => 0,
			])
			->add('vat_added', HiddenType::class, [
				'empty_data' => 0,
			])
			->add('vat_removed', HiddenType::class, [
				'empty_data' => 0,
			])
			->add('is_custom', HiddenType::class, [
				'empty_data' => 0
			])
			->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
				$form_data = $event->getData();
				if (empty($form_data)) {
					return;
				}
				$form_data_new = $this->calculateAll($form_data);
				$event->setData($form_data_new);
				return;
			});
		return $form->getForm();
	}
	
	/**
	 * Function for calculating the VAT output
	 */
	private function calculateAll ($form_data) {
		if (!empty($form_data['based_on']) && (isset($form_data['vat_added']) || isset($form_data['vat_removed'])) && (!empty($form_data['country_rate']) || !empty($form_data['vat_rate']))) {
			if (!empty($form_data['country_rate'])) {
				$entity_manager = $this->getDoctrine()->getManager();
				$country = $entity_manager->getRepository(CountryRate::class)->findById(intval($form_data['country_rate']));
				if (!empty($country[0])) {
					$form_data['vat_rate'] = $country[0]->getRate();
				}
			} else {
				unset($form_data['country_rate']);
			}
			$based_on = floatval($form_data['based_on']);
			$vat_rate = floatval($form_data['vat_rate']);
			$gross_amount = 0;
			if (!empty($form_data['vat_added'])) {
				$net_amount = $based_on;
				$vat_amount = $net_amount * ($vat_rate / 100);
				$gross_amount = $net_amount + $vat_amount;
			} else {
				$gross_amount = $based_on;
				$net_amount = $based_on / (($vat_rate + 100) / 100);
				$form_data['net_amount'] = $net_amount;
				$vat_amount = $gross_amount - $net_amount;
			}
			$form_data['gross_amount'] = round($gross_amount, 2);
			$form_data['vat_amount'] = round($vat_amount, 2);
			$form_data['net_amount'] = round($net_amount, 2);
			$form_data['based_on'] = $based_on;
			$form_data['vat_rate'] = $vat_rate;
			if (!empty($form_data['country_rate'])) $form_data['country_rate'] = intval($form_data['country_rate']);
		}
		return $form_data;
	}
}