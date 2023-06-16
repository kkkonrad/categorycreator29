<?php
namespace Kkkonrad\Categorycreator29\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;


class CreateCategoryCommand extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_registry = $registry;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
         $this->setName("kkkonrad:create_category");
         $this->setDescription("Create new Magento 2 category from console command");
         $this->setDefinition([
             new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
             new InputOption(self::NAME_OPTION, "-d", InputOption::VALUE_NONE, "Option functionality")
         ]);
         parent::configure();
    }

    protected function createCategory($catName, $option)
    {
         $parentId = $this->_storeManager->getStore()->getRootCategoryId(); 
         $parentCategory = $this->_categoryFactory->create()->load($parentId);
 
         $category = $this->_categoryFactory->create();
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
             return "Category Id: " . $category->getId() . " with name: '" . $catName . "' created.";
         } else {
            if($option) {
                $this->_registry->register("isSecureArea", true);
                $cate->delete();
                return "Category with name: '" . $catName . "' has been deleted.";
            } else {
                return "Category with name: '" . $catName . "' already exists.";
            }
         }      
    }

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
            return Cli::RETURN_SUCCESS;
            
        }
        $output->writeln($this->createCategory($catName, $option));
        return Cli::RETURN_SUCCESS;
    }

}
