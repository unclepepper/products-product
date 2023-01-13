<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace App\Module\Products\Product\Controller\Admin;

use App\Module\Products\Product\Entity;
use App\Module\Products\Product\UseCase\Admin\Delete\DeleteForm;
use App\Module\Products\Product\UseCase\Admin\Delete\ProductDTO;
use App\Module\Products\Product\UseCase\ProductAggregate;
use App\System\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('"ROLE_ADMIN" in role_names or "ROLE_PRODUCT_DELETE" in role_names'))]
final class DeleteController extends AbstractController
{
	
	#[Route('/admin/product/delete/{id}', name: 'admin.product.delete', methods: ['POST', 'GET'])]
	public function delete(
		Request $request,
		ProductAggregate $handler,
		#[MapEntity] Entity\Event\ProductEvent $Event,
		EntityManagerInterface $entityManager
	) : Response
	{
		
		$product = new ProductDTO();
		$Event->getDto($product);
		
		$Info = $entityManager->getRepository(Entity\Info\Info::class)->findOneBy(['product' => $Event->getProduct()]);
		$Info->getDto($product->getInfo());
		
		$form = $this->createForm(DeleteForm::class, $product, [
			'action' => $this->generateUrl('Product:admin.product.delete', ['id' => $product->getEvent()]),
		]);
		$form->handleRequest($request);
		
		
		if($form->isSubmitted() && $form->isValid())
		{
			if($form->has('delete'))
			{
				$handle = $handler->handle($product);
				
				if($handle)
				{
					$this->addFlash('success', 'admin.product.delete.success', 'products.product');
					return $this->redirectToRoute('Product:admin.product.index');
				}
			}
			
			$this->addFlash('danger', 'admin.product.delete.danger', 'products.product');
			return $this->redirectToRoute('Product:admin.product.index');
			
			//return $this->redirectToReferer();
		}
		
		return $this->render
		(
			[
				'form' => $form->createView(),
				'name' => $Event->getNameByLocale($this->getLocale()), /*  название согласно локали  */
			]
		);
	}
	
}