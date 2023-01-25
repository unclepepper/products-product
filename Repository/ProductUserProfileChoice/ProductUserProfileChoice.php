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

namespace BaksDev\Products\Product\Repository\ProductUserProfileChoice;

use BaksDev\Products\Product\Entity\SettingsProduct;
use App\Module\Users\Auth\Email\Type\Status\AccountStatus;
use App\Module\Users\Auth\Email\Type\Status\AccountStatusEnum;
use App\Module\Users\Profile\UserProfile\Entity as UserProfileEntity;
use App\Module\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use App\Module\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use App\Module\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;

use Doctrine\ORM\EntityManagerInterface;
use App\Module\Users\Auth\Email\Entity as AccountEntity;


final class ProductUserProfileChoice implements ProductUserProfileChoiceInterface
{
    
    private EntityManagerInterface $entityManager;
    private AccountStatus $account_status;
    private UserProfileStatus $status;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->account_status = new AccountStatus(AccountStatusEnum::ACTIVE);
        $this->status = new UserProfileStatus(UserProfileStatusEnum::ACTIVE);
    }
    
    /** Получаем список профилей пользователей, доступных к созданию карточек */
    public function get()
    {
        $select = sprintf('new %s(user_profile.id, personal.username)', UserProfileUid::class);
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select($select);
        
        $qb->from(UserProfileEntity\UserProfile::class, 'user_profile');
        
        $qb->join(
          UserProfileEntity\Info\Info::class,
          'info',
          'WITH',
          'info.profile = user_profile.id AND info.status = :status');
        
        $qb->setParameter('status', $this->status, UserProfileStatus::TYPE);
        
        $qb->join(
          UserProfileEntity\Event\Event::class,
          'event',
          'WITH',
          'event.id = user_profile.event AND event.profile = user_profile.id');
        
        $qb->join(
          UserProfileEntity\Personal\Personal::class,
          'personal',
          'WITH',
          'personal.event = event.id');
        
        /* Тип профиля, имеющий доступ к созданию карточек */
        
        $qb->join(
          SettingsProduct::class,
          'profile',
          'WITH',
          'profile.profile = event.type');
        
        
        $qb->join(
          AccountEntity\Account::class,
          'account',
          'WITH',
          'account.id = info.user');
        
        $qb->join(
          AccountEntity\Status\AccountStatus::class,
          'status',
          'WITH',
          'status.event = account.event AND status.status = :account_status');
        
        $qb->setParameter('account_status', $this->account_status, AccountStatus::TYPE);
        
        return $qb->getQuery()->toIterable();
    }
    
}