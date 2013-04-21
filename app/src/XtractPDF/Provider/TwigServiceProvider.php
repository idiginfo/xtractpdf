<?php

namespace XtractPDF\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\Provider\TwigServiceProvider as SilexTwigServiceProvider;

use Symfony\Component\HttpFoundation\Request;
use Twig_SimpleFunction, Twig_Extension_Debug;

class TwigServiceProvider extends SilexTwigServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Silex\Application
     */
    private $app;

    // --------------------------------------------------------------

    /**
     * {@inherit}
     */
    public function boot(Application $app)
    {
        $this->app = $app;
        $app->before(array($this, 'setupTwig'), Application::EARLY_EVENT);

        return parent::boot($app);
    }

    // --------------------------------------------------------------

    /**
     * Setup Twig
     *
     * @param Symfony\HttpFoundation\Request
     */
    public function setupTwig(Request $request)
    {
        //Refer to the App
        $app =& $this->app;

        $app['twig'] = $app->share($app->extend('twig', function($twig, $app) use ($request) {

            //Get some URLs
            $baseUrl    = $request->getSchemeAndHttpHost() . $request->getBasePath();
            $appUrl     = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
            $currentUrl = $appUrl . $request->getPathInfo();

            //Register URLs as globals
            $twig->addGlobal('base_url',    $baseUrl);
            $twig->addGlobal('site_url',    $appUrl);
            $twig->addGlobal('current_url', $currentUrl);     

            //debug method
            if ($app['debug'] == true) {
                $twig->addExtension(new Twig_Extension_Debug());
            }

            return $twig;
        }));           
    }
}

/* EOF: TwigServiceProvider.php */