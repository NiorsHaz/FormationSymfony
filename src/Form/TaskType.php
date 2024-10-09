<?php

namespace App\Form;

use App\Entity\Task;
use DateTimeImmutable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre'
            ])
            ->add('slug', TextType::class, [
                'required' => false,
            ])
            ->add('description')
            ->add('estimates', TextType::class, [
                'label' => 'Estimation'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoSlug(...))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->attachTimestamps(...))
        ;
    }

    private function autoSlug(PreSubmitEvent $event) : void {
        $data = $event->getData();

        if(empty($data['slug'])) {
            $slugger = new AsciiSlugger();
            // Slugify title
            $data['slug'] = strtolower($slugger->slug($data['title']));
            $event->setData($data);
        }
    }

    private function attachTimestamps(PostSubmitEvent $event) : void {
        $data = $event->getData();

        if(! ($data instanceof Task)) {
            return;
        }

        $data->setUpdatedAt(new DateTimeImmutable());

        if(!$data->getId()) {
            $data->setCreatedAt(new DateTimeImmutable());
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
