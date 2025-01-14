<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Propel\Bundle\PropelBundle\PropelBundle(),
            new Creonit\PropelSchemaConverterBundle\CreonitPropelSchemaConverterBundle(),
            new Creonit\MediaBundle\CreonitMediaBundle(),
            new Gregwar\ImageBundle\GregwarImageBundle(),
            new Creonit\PageBundle\CreonitPageBundle(),
            new Creonit\ContentBundle\CreonitContentBundle(),
            new Creonit\MailingBundle\CreonitMailingBundle(),
            new Creonit\UserBundle\CreonitUserBundle(),
            new Creonit\AdminBundle\CreonitAdminBundle(),
            new Creonit\RestBundle\CreonitRestBundle(),
            new Creonit\MarkupBundle\CreonitMarkupBundle(),
            new Cocur\Slugify\Bridge\Symfony\CocurSlugifyBundle(),
            new AppBundle\AppBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
