<?php

namespace App\Controller\Admin;

use App\Entity\SoftwareVersion;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class SoftwareVersionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SoftwareVersion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Software Version')
            ->setEntityLabelInPlural('Software Versions')
            ->setDefaultSort(['name' => 'ASC', 'systemVersion' => 'ASC'])
            ->setSearchFields(['name', 'systemVersion', 'systemVersionAlt'])
            ->setPaginatorPageSize(50)
            ->setHelp('new', 'Add a new firmware version. The LCI fields are auto-calculated from the product name.')
            ->setHelp('edit', 'Edit a firmware version. The LCI fields are auto-calculated from the product name.');
    }

    public function configureFields(string $pageName): iterable
    {
        $productNames = [
            'MMI Prime CIC' => 'MMI Prime CIC',
            'MMI Prime NBT' => 'MMI Prime NBT',
            'MMI Prime EVO' => 'MMI Prime EVO',
            'MMI PRO CIC' => 'MMI PRO CIC',
            'MMI PRO NBT' => 'MMI PRO NBT',
            'MMI PRO EVO' => 'MMI PRO EVO',
            'LCI MMI Prime CIC' => 'LCI MMI Prime CIC',
            'LCI MMI Prime NBT' => 'LCI MMI Prime NBT',
            'LCI MMI Prime EVO' => 'LCI MMI Prime EVO',
            'LCI MMI PRO CIC' => 'LCI MMI PRO CIC',
            'LCI MMI PRO NBT' => 'LCI MMI PRO NBT',
            'LCI MMI PRO EVO' => 'LCI MMI PRO EVO',
        ];

        yield IdField::new('id')->onlyOnIndex();

        yield ChoiceField::new('name', 'Product Name')
            ->setChoices($productNames)
            ->setHelp('Select the hardware product group. LCI fields are auto-calculated from this.')
            ->setRequired(true);

        yield TextField::new('systemVersion', 'System Version')
            ->setHelp('Full version string, e.g. "v3.3.7.mmipri.c"')
            ->setRequired(true);

        yield TextField::new('systemVersionAlt', 'Version Alt (Lookup Key)')
            ->setHelp('Version without "v" prefix, e.g. "3.3.7.mmipri.c". This is what customers enter.')
            ->setRequired(true);

        yield TextField::new('link', 'General Download Link')
            ->setHelp('Main firmware download folder URL (Google Drive)')
            ->hideOnIndex();

        yield TextField::new('stLink', 'ST Download Link')
            ->setHelp('ST hardware variant download URL')
            ->hideOnIndex();

        yield TextField::new('gdLink', 'GD Download Link')
            ->setHelp('GD hardware variant download URL')
            ->hideOnIndex();

        yield BooleanField::new('isLatest', 'Latest Version?')
            ->setHelp('⚠️ Only ONE version per product group should be marked as latest!');

        yield BooleanField::new('isLci', 'LCI?')
            ->setFormTypeOption('disabled', true)
            ->setHelp('Auto-calculated from product name')
            ->hideOnForm();

        yield TextField::new('lciHwType', 'LCI HW Type')
            ->setFormTypeOption('disabled', true)
            ->setHelp('Auto-calculated: CIC, NBT, or EVO')
            ->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('name')->setChoices([
                'MMI Prime CIC' => 'MMI Prime CIC',
                'MMI Prime NBT' => 'MMI Prime NBT',
                'MMI Prime EVO' => 'MMI Prime EVO',
                'MMI PRO CIC' => 'MMI PRO CIC',
                'MMI PRO NBT' => 'MMI PRO NBT',
                'MMI PRO EVO' => 'MMI PRO EVO',
                'LCI MMI Prime CIC' => 'LCI MMI Prime CIC',
                'LCI MMI Prime NBT' => 'LCI MMI Prime NBT',
                'LCI MMI Prime EVO' => 'LCI MMI Prime EVO',
                'LCI MMI PRO CIC' => 'LCI MMI PRO CIC',
                'LCI MMI PRO NBT' => 'LCI MMI PRO NBT',
                'LCI MMI PRO EVO' => 'LCI MMI PRO EVO',
            ]))
            ->add(BooleanFilter::new('isLatest'))
            ->add(BooleanFilter::new('isLci'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
