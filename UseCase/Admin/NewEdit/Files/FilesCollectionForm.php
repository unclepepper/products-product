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

namespace App\Module\Products\Product\UseCase\Admin\NewEdit\Files;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FilesCollectionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
          ->add(
            'file', FileType::class,
            [
              'label' => false,
              'required' => false,
              'attr' => ['accept' => ".doc, .docx, .xls, .xlsx, .pdf"],
            ]
          );
    
        $builder->add
        (
          'DeleteFile',
          ButtonType::class,
          [
            'label_html' => true,
            'attr' =>
              ['class' => 'btn btn-sm btn-icon btn-light-danger del-item-file'],
          ]);
    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => FilesCollectionDTO::class,
          ]);
    }
    
}
