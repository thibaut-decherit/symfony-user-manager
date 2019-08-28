<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * Class PasswordChangeType
 * @package AppBundle\Form\User
 */
class PasswordChangeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'user.current_password',
                'mapped' => false,
                'required' => false,
                'constraints' => array(
                    new UserPassword([
                        'message' => 'form_errors.user.wrong_password',
                        'groups' => ['Password_Change']
                    ]),
                ),
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'form_errors.user.repeat_password',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options' => array('label' => 'user.new_password'),
                'second_options' => array('label' => 'user.new_password_repeat'),
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'validation_groups' => array('Password_Change')
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'appbundle_user';
    }
}
