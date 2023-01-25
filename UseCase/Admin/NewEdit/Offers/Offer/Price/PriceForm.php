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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Offers\Offer\Price;


use App\System\Type\Currency\Currency;
use App\System\Type\Money\Money;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


final class PriceForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add
        ('price', MoneyType::class,
         [
           'required' => false,
           'currency' => false,
         ]
        );
    
        $builder->get('price')->addModelTransformer(
          new CallbackTransformer(
            function ($price)
            {
                return $price instanceof Money ? $price->getValue() : $price;
            },
            function ($price) {
            
                return new Money($price);
            }
          ));
    
        $builder->add
        ('currency', ChoiceType::class, [
          'choices' => Currency::cases(),
          'choice_value' => function (?Currency $currency) { return $currency?->getValue(); },
          'choice_label' => function (?Currency $currency) {  return $currency?->getName(); },
          'translation_domain' => 'currency',
          'label' => false,
        ]);


    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => PriceDTO::class,
          ]);
    }
    
}
