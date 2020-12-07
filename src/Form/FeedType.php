<?php


namespace App\Form;


use App\Entity\FeedEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('sourceUrl', TextType::class, [
				'label' => 'Import Offer JSON URL (required)',
				'help' => 'Please enter a valid offer json URL',
				'attr' => ['placeholder' => 'http://127.0.0.1:8000/c51.json'],
			])
			->add('skipError', ChoiceType::class, [
				'label' => 'Skip Error Offer (required)',
				'choices' => array_flip([0 => 'No', 1 => 'Yes']),
				'attr' => ['class' => 'chosen'],
				'help' => 'Import will skip error offer data (offerId is numeric and > 0, cash back is > 0)',
			])
			->add('forceUpdate', ChoiceType::class, [
				'label' => 'Force Update (required)',
				'choices' => array_flip([false => 'No', true => 'Yes']),
				'attr' => ['class' => 'chosen'],
				'help' => 'Update existing offer data',
			])
			;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => FeedEntity::class,
		]);
	}
}