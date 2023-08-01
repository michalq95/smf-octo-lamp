<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class CompanyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Company::class;
    }
    public function configureFields(string $pageName): iterable
    {
        return [

            AssociationField::new('owner')->autocomplete(),
            Field::new('title'),
            Field::new('slug'),
            Field::new('published')->hideOnForm(),
            Field::new('content'),
            Field::new('email'),
            Field::new('address'),
            Field::new('loc_x'),
            Field::new('loc_y'),
            AssociationField::new('images')->autocomplete(),
            AssociationField::new('offers')->hideOnForm()->autocomplete(),

        ];
    }
}