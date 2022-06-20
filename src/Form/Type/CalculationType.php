<?php

namespace App\Form\Type;

use App\Entity\CountryRate;
use App\Repository\CountryRateRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class CalculationType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$countries = $this->getCountries();
		$builder
            ->add('country_rate', ChoiceType::class, [
                'choices'  => $countries,
                'choice_label' => function(?Country $country) {
                    return $country ? $country->getName() : '';
                },
                'choice_attr' => function($country, $key, $value) {
                    return [
                        'class' => 'country-option',
                        'data-code' => $country->getCode(),
						'data-rate' => $country->getRate()
                    ];
                }
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])->getForm();
		
		 $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
			$data = $event->getData();
			
			$event->setData($data);
		 });
	}
	
	public function getCountries(CountryRateRepository $crr) {
		$countries = $crr->findAll();
		return $countries;
	}
}