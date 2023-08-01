<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class OfferCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Offer::class;
    }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();
        yield Field::new('title');
        yield Field::new('content');
        yield Field::new('published')->hideOnForm();
        yield Field::new('slug')->hideOnForm();
        yield Field::new('bracket_low');
        yield Field::new('bracket_high');
        yield Field::new('currency');
        yield Field::new('status');
        yield AssociationField::new('company')->autocomplete();
        yield CollectionField::new('tags')->hideOnForm();
        yield AssociationField::new('tags')->autocomplete()->hideOnIndex()->hideOnDetail();
    }
}