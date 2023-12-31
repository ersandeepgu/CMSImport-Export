<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sandeep\CmsExport\Controller\Adminhtml\Index;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Magento\Backend\App\Action
{

    protected $uploaderFactory;

    protected $_locationFactory; 

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
        

    ) {
       parent::__construct($context);
       $this->_fileFactory = $fileFactory;
       $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
       $this->blockRepository = $blockRepository;
       $this->searchCriteriaBuilder = $searchCriteriaBuilder;
       parent::__construct($context);
    }

     public function execute()
    {   


        $searchCriteria = $this->searchCriteriaBuilder->create();
        $cmsBlocks = $this->blockRepository->getList($searchCriteria)->getItems();


        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/block-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = ['title','identifier','content','store_id','is_active'];

            foreach ($columns as $column) 
            {
                $header[] = $column; //storecolumn in Header array
            }

         $stream->writeCsv($header);

         foreach($cmsBlocks as $item){
            $itemData = [];
            $itemData[] = $item->getTitle();
            $itemData[] = $item->getIdentifier();
            $itemData[] = $item->getContent();
            $itemData[] = implode(",",$item->getStoreId());
            $itemData[] = $item->getIsActive();
            $stream->writeCsv($itemData);

         }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'blocks-data-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);

    }
}