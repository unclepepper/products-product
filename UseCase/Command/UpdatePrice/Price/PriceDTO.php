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

namespace App\Module\Products\Product\UseCase\Command\UpdatePrice\Price;

use App\Module\Products\Product\Entity\Price\PriceInterface;
use App\System\Type\Currency\Currency;
use App\System\Type\Currency\CurrencyEnum;
use App\System\Type\Measurement\Measurement;
use App\System\Type\Money\Money;
use Symfony\Component\Validator\Constraints as Assert;

final class PriceDTO implements PriceInterface
{
    /** Стоимость */
    #[Assert\NotBlank]
    private ?Money $price;
    

    public function getPrice() : ?Money
    {
        return $this->price;
    }
    

    public function setPrice(Money|float $price) : void
    {
        $this->price = $price instanceof Money ? $price : new Money($price);
    }
}