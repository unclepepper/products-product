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


namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Variation\Image;

use App\Module\Product\Entity\Product\Offers\Offer\Image;
use App\Module\Product\Entity\Product\Photo;
use ImageCollectionDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductOfferVariationImageCollectionForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		$builder->add('file', FileType::class,
			[
				'label' => false,
				'required' => false,
				'attr' => ['accept' => ".png, .jpg, .jpeg, .webp, .gif"],
			]
		);
		
		$builder->add('root', RadioType::class, ['required' => false]);
		
		$builder->add
		(
		  'DeleteVariationPhoto',
		  ButtonType::class,
		  [
			'label_html' => true,
		  ]);
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults([
			'data_class' => ProductVariationImageCollectionDTO::class,
		]);
	}
	
}
