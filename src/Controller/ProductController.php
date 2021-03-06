<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\HttpFoundation\File\File;

use App\Service\FileUploader;

/**
 * @Route("/product", name="product_")
 */
class ProductController extends Controller
{
    /**
     * @Route("/", name="index")
     *
     * @return Response
     */
    public function index()
    {
        $products = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();

        return $this->render('product/index.html.twig', ['products' => $products]);
    }

    /**
     * @Route("/new", name="new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->get('username')->setData($this->getUser()->getUsername());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle file
            $file = $product->getImage();
            $fileName = $fileUploader->upload($file);
            $product->setImage($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show")
     * @Method("GET")
     */
    public function show(Product $product)
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER')")
     */
    public function edit(Request $request, Product $product, FileUploader $fileUploader)
    {
        $fileName = $product->getImage();
//        $product->setBrochure(
//            new File($this->getParameter('image_directory').'/'.$fileName)
//        );

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // handle file
            $file = $product->getImage();
            $fileName = $fileUploader->upload($file);
            $product->setImage($fileName);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'fileName' => $fileName,

        ]);
    }

    /**
     * @Route("/{id}", name="delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_USER')")
     */
    public function delete(Request $request, Product $product)
    {
        if (!$this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('product_index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('product_index');
    }
}
