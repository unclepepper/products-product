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

namespace BaksDev\Products\Product\Controller\User;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Orders\Order\UseCase\User\Basket\Add\OrderProductDTO;
use BaksDev\Orders\Order\UseCase\User\Basket\Add\OrderProductForm;
use BaksDev\Products\Product\Entity;
use BaksDev\Products\Product\Repository\ProductAlternative\ProductAlternativeInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByValueInterface;
use BaksDev\Products\Product\Repository\ProductDetailOffer\ProductDetailOfferInterface;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class DetailController extends AbstractController
{
    #[Route('/product/{url}/{offer}/{variation}/{modification}', name: 'user.detail')]
    public function index(
        Request $request,
        #[MapEntity(mapping: ['url' => 'url'])] Entity\Info\ProductInfo $info,
        ProductDetailByValueInterface $productDetail,
        ProductDetailOfferInterface $productDetailOffer,
        ProductAlternativeInterface $productAlternative,
        $offer = null,
        $variation = null,
        $modification = null,
    ): Response {
        //
        $productCard = $productDetail->fetchProductAssociative(
            $info->getProduct(),
            $offer,
            $variation,
            $modification,
        );

        /** Другие ТП данного продукта */
        $productOffer = $productDetailOffer->fetchProductOfferAssociative($info->getProduct());

        /** Статус, если товара с ТП, вариантом или модификацией не существует */
        $status = 200;

        $NOW = new DateTimeImmutable();

        if (
            !$productCard ||
            ($offer !== null && $productCard['product_offer_value'] === null) ||
            ($variation !== null && $productCard['product_variation_value'] === null) ||
            ($modification !== null && $productCard['product_modification_value'] === null) ||
            $productCard['active'] === false ||
            new DateTimeImmutable($productCard['active_from']) > $NOW ||
            new DateTimeImmutable($productCard['active_to']) < $NOW

        ) {
            $status = 404;
        }

        if ($status === 404)
        {
            return $this->render(
                [
                'card' => $productCard,
                'offers' => $productOffer,

                'offer' => $offer,
                'variation' => $variation,
                'modification' => $modification,
            ],
                //fileName: 'notfound/template.html.twig',
                routingName: 'user.detail.notfound',
                response: new Response(status: $status)
            );
        }

        /* Удаляем сессию фильтра каталога */
        $request->getSession()->set('catalog_filter', null);

        /** Список альтернатив  */
        $alternativeProperty = json_decode($productCard['category_section_field'], false, 512, JSON_THROW_ON_ERROR);
        /* получаем свойства, учавствующие в фильтре альтернатив */
        $alternativeField = array_filter($alternativeProperty, function ($v) {
            return $v->field_alternative === true;
        }, ARRAY_FILTER_USE_BOTH);

        //dump($alternativeField);

        $alternative = null;
        if (!empty($productCard['product_offer_value']))
        {
            $alternative = $productAlternative->fetchAllAlternativeAssociative(
                $productCard['product_offer_value'],
                $productCard['product_variation_value'],
                $productCard['product_modification_value'],
                $alternativeField
            );
        }

        /* Корзина */

        $AddProductBasketDTO = new OrderProductDTO();
        $form = $this->createForm(OrderProductForm::class, $AddProductBasketDTO, [
            'action' => $this->generateUrl(
                'Orders:user.add',
                [
                    'product' => $productCard['event'],
                    'offer' => $productCard['product_offer_uid'],
                    'variation' => $productCard['product_variation_uid'],
                    'modification' => $productCard['product_modification_uid'],
                ]
            ),
        ]);

        return $this->render([
            'card' => $productCard,
            'offers' => $productOffer,
            'alternative' => $alternative,
            'offer' => $offer,
            'variation' => $variation,
            'modification' => $modification,
            'basket' => $form->createView(),
        ]);
    }
}
