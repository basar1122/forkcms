<?php

namespace ForkCMS\Bundle\InstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use ForkCMS\Bundle\InstallerBundle\Service\ForkInstaller;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Backend\Core\Engine\Model as BackendModel;

/**
 * Builds the form to select modules to install
 */
class ModulesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'modules',
                'choice',
                [
                    'choices' => $this->getInstallableModules(),
                    'expanded' => true,
                    'multiple' => true,
                ]
            )
            ->add(
                'example_data',
                'checkbox',
                [
                    'label' => 'Install example data',
                    'required' => false,
                ]
            )
            ->add(
                'different_debug_email',
                'checkbox',
                [
                    'label' => 'Use a specific debug email address',
                    'required' => false,
                ]
            )
            ->add(
                'debug_email',
                'email',
                [
                    'required' => false,
                ]
            )
        ;

        // make sure the required modules are selected when submitting
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();

                // add the modules array if it doesn't exit
                if (!isset($data['modules'])) {
                    $data['modules'] = [];
                }

                $data['modules'] = array_merge(
                    $data['modules'],
                    ForkInstaller::getRequiredModules()
                );

                $event->setData($data);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ForkCMS\Bundle\InstallerBundle\Entity\InstallationData',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'install_modules';
    }

    /**
     * Get all the modules that can be installed
     *
     * @return array The modules
     */
    protected function getInstallableModules()
    {
        $modules = array_unique(array_merge(
            ForkInstaller::getRequiredModules(),
            BackendModel::getModulesOnFilesystem(false)
        ));
        $this->removeHiddenModules($modules);

        return array_combine($modules, $modules);
    }

    /**
     * Make sure the required modules are checked and can't be desalbed
     *
     * @param FormView      $view    The FormView generated by Symfony
     * @param FormInterface $form    The form itself
     * @param array         $options The array options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['modules']->children as $module) {
            if (in_array($module->vars['value'], ForkInstaller::getRequiredModules())) {
                $module->vars['attr']['disabled'] = 'disabled';
                $module->vars['checked'] = true;
            }
        }
    }

    /**
     * Remove the hidden modules from the modules array
     *
     * @param array $modules The modules we wan't to clean up
     */
    protected function removeHiddenModules(&$modules)
    {
        foreach ($modules as $key => $module) {
            if (in_array($module, ForkInstaller::getHiddenModules())) {
                unset($modules[$key]);
            }
        }
    }
}
