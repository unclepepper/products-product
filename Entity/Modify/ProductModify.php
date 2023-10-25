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

namespace BaksDev\Products\Product\Entity\Modify;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Ip\IpAddress;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Модификаторы событий Product */


#[ORM\Entity]
#[ORM\Table(name: 'product_modify')]
#[ORM\Index(columns: ['action'])]
class ProductModify extends EntityEvent
{
	public const TABLE = 'product_modify';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\OneToOne(inversedBy: 'modify', targetEntity: ProductEvent::class)]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private ProductEvent $event;
	
	/** Модификатор */
	#[ORM\Column(type: ModifyAction::TYPE, nullable: false)]
	private ModifyAction $action;
	
	/** Дата */
	#[ORM\Column(name: 'mod_date', type: Types::DATETIME_IMMUTABLE, nullable: false)]
	private DateTimeImmutable $modDate;
	
	/** ID пользователя  */
	#[ORM\Column(type: UserUid::TYPE, nullable: true)]
	private ?UserUid $usr = null;
	
	/** Ip адрес */
	#[ORM\Column(type: IpAddress::TYPE, nullable: false)]
	private IpAddress $ip;
	
	/** User-agent */
	#[ORM\Column(type: Types::TEXT, nullable: false)]
	private string $agent;
	
	
	public function __construct(ProductEvent $event)
	{
		$this->event = $event;
		$this->modDate = new DateTimeImmutable();
		
		$this->action = new ModifyAction(ModifyActionNew::class);
		$this->ip = new IpAddress('127.0.0.1');
		$this->agent = 'console';
		
	}

	public function __clone() : void
	{
		$this->modDate = new DateTimeImmutable();
		$this->action = new ModifyAction(ModifyActionUpdate::class);
		$this->ip = new IpAddress('127.0.0.1');
		$this->agent = 'console';
	}

    public function __toString(): string
    {
        return (string) $this->event;
    }

	public function getDto($dto): mixed
	{
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

		if($dto instanceof ProductModifyInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto): mixed
	{
		if($dto instanceof ProductModifyInterface || $dto instanceof self)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function upModifyAgent(IpAddress $ip, ?string $agent) : void
	{
		$this->ip = $ip;
		$this->agent = $agent ?: 'console';
		$this->modDate = new DateTimeImmutable();
	}
	
	
	public function setUsr(UserUid|User|null $usr) : void
	{
		$this->usr = $usr instanceof User ? $usr->getId() : $usr;
	}
	
	
	public function equals(ModifyActionEnum $action) : bool
	{
		return $this->action->equals($action);
	}
	
}
