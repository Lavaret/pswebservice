<?php
namespace App\Controller;

use App\Entity\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Form\Type\TokenType;
use App\Service\PrestaShopWebservice;
use App\Service\Messages;

class MainController extends AbstractController
{


    const PRODUCT_RESOURCE = 'products';

    const STOCK_RESOURCE = 'stock_availables';

	 /**
     * @Route("/", name="config")
     */
    public function config(Request $request)
    {
        $session = new Session();
        $session->start();

        $token = new Token();

        $form = $this->createForm(TokenType::class, $token);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $token = $form->getData();
            $session->set('DEBUG', $token->isDebug());
            $session->set('AUTH_KEY', $token->getAuthKey());
            $session->set('SHOP_PATH', $token->getShopPath());

            return $this->redirectToRoute('product_list');
        }

        return $this->render('/config.html.twig', [
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/products", name="product_list")
     */
    public function list()
    {
        $session = new Session();
        $session->start();
        
        $result = [];

        try {

            $webService = new PrestaShopWebservice(
                $session->get('SHOP_PATH'), 
                $session->get('AUTH_KEY'), 
                $session->get('DEBUG')
            );
            
            // option array for the Webservice
            $opt['resource'] = self::STOCK_RESOURCE;
            
            // Call
            $xml = $webService->get($opt);

            $resources = $xml->stock_availables->stock_available;

            foreach ($resources as $resource) {

                //get stock array
                $opt['id'] = $resource->attributes();

                $xml = $webService->get($opt);

                $result[(int) $resource->attributes()]['stock'] = $xml->stock_available;
            }
            
            foreach ($result as $key => $value) {

                $opt['id'] = (int) $value['stock']->id_product;
                //get quantity
                $opt['resource'] = self::PRODUCT_RESOURCE;

                $xml = $webService->get($opt);
    
                $result[$key]['product'] = $xml->product;

            }

        } catch (\Exception $e) {
            $token = new Token();

            // Here we are dealing with errors
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) $session->getFlashBag()->add('error', Messages::ID_ERROR);
            else if ($trace[0]['args'][0] == 401) $session->getFlashBag()->add('error', Messages::AUTH_KEY_ERROR);
            else $session->getFlashBag()->add('error', Messages::UNKNOWN_ERROR);

            $form = $this->createForm(TokenType::class, $token);

            return $this->render('/config.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->render('/list.html.twig', [
            'result' => $result
        ]);
    }

     /**
     * @Route("/edit", methods={"POST"})
     */
    public function edit(Request $request)
    {
        $session = new Session();
        $session->start();

        $id = (int) $request->get('id');
        $stock = (int) $request->get('stock');

        if(!$id || !$stock && $stok !== 0) {
            return $this->redirectToRoute('product_list');
        }

        try {

            $webService = new PrestaShopWebservice(
                $session->get('SHOP_PATH'), 
                $session->get('AUTH_KEY'), 
                $session->get('DEBUG')
            );
            
            // option array for the Webservice
            $opt['resource'] = self::STOCK_RESOURCE;
            $opt['id_product'] = $id;

            $xml = $webService->get($opt);

            $xml->stock_available->quantity = $stock;

            $opt = array('resource' => self::STOCK_RESOURCE);
            $opt['putXml'] = $xml->asXML();
            $opt['id'] = $id;
            $xml = $webService->edit($opt);

        } catch (PrestaShopWebserviceException $e) {
            $token = new Token();

            // Here we are dealing with errors
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) $session->getFlashBag()->add('error', Messages::ID_ERROR);
            else if ($trace[0]['args'][0] == 401) $session->getFlashBag()->add('error', Messages::AUTH_KEY_ERROR);
            else $session->getFlashBag()->add('error', Messages::UNKNOWN_ERROR);

            $form = $this->createForm(TokenType::class, $token);

            return $this->render('/config.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('product_list');
    }
}