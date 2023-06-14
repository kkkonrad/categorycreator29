<?php
declare(strict_types=1);

namespace Kkkonrad\Categorycreator29\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class Createcategory extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $catName = $input->getArgument(self::NAME_ARGUMENT);
        $option = $input->getOption(self::NAME_OPTION);

        if(!$catName) {
            $output->writeln("Please specify Category Name as first argument:");
            $output->writeln("e.q.: bin/magento kkkonrad:createcategory CatName");
            exit;
        }

        // get the current stores root category
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
        
        $parentId = $storeManager->getStore()->getRootCategoryId();

        $parentCategory = $categoryFactory->create()->load($parentId);

        $category = $categoryFactory->create();
        $cate = $category->getCollection()
            ->addAttributeToFilter('name', $catName)
            ->getFirstItem();

        if (!$cate->getId()) {
            $cleanCatName = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($catName))))));
            $category->setPath($parentCategory->getPath())
                ->setParentId($parentId)
                ->setName($catName)
                ->setUrlKey($cleanCatName)
                ->setIsActive(true);
            $category->save();
            $output->writeln("Category Id: " . $category->getId() . " with name: '" . $catName . "' created.");
        } else {
            $output->writeln("Category with name: '" . $catName . "' already exists.");
        }       
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("kkkonrad:createcategory");
        $this->setDescription("Create new Magento 2 category from console command");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
}

