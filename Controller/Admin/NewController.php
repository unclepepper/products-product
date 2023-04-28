<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Products\Product\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\Security\RoleSecurity;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Category\CategoryCollectionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductForm;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\ProductHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[RoleSecurity('ROLE_PRODUCT_NEW')]
final class NewController extends AbstractController
{
    #[Route('/admin/product/new/{id}', name: 'admin.newedit.new', defaults: ['id' => null], methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProductHandler $handler,
        ?ProductEventUid $id = null,
    ): Response {
        $ProductDTO = new ProductDTO();

        // Если передан идентификатор события - копируем
        if ($id) {
            $Event = $entityManager->getRepository(Entity\Event\ProductEvent::class)->find($id);

            if ($Event) {
                $Event->getDto($ProductDTO);
                $ProductDTO->setId(new ProductEventUid());
            }
        }

        // Если передана категория - присваиваем для подгрузки настроект (свойства, ТП)
        if ($request->get('category')) {
            $CategoryCollectionDTO = new CategoryCollectionDTO();
            $CategoryCollectionDTO->rootCategory();
            $CategoryCollectionDTO->setCategory(new ProductCategoryUid($request->get('category')));
            $ProductDTO->addCategory($CategoryCollectionDTO);
        }

        // Форма добавления
        $form = $this->createForm(ProductForm::class, $ProductDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Product = $handler->handle($ProductDTO);

            if ($Product instanceof Entity\Product) {
                $this->addFlash('success', 'admin.success.new', 'admin.products.product');

                return $this->redirectToRoute('Product:admin.index');
            }

            $this->addFlash('danger', 'admin.danger.new', 'admin.products.product', $Product);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
