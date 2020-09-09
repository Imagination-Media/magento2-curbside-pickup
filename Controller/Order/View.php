<?php

/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\CurbsidePickup\Controller\Order;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class View
 * @package ImaginationMedia\CurbsidePickup\Controller\Order
 */
class View implements ActionInterface
{
    /**
     * @var PageFactory
     */
    protected PageFactory $pageFactory;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var RedirectInterface
     */
    protected RedirectInterface $redirect;

    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * @var ManagerInterface
     */
    protected ManagerInterface $message;

    /**
     * View constructor.
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     * @param ManagerInterface $message
     */
    public function __construct(
        PageFactory $pageFactory,
        RequestInterface $request,
        RedirectInterface $redirect,
        ResponseInterface $response,
        ManagerInterface $message
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->message = $message;
    }

    /**
     * @return ResponseInterface|Page
     */
    public function execute()
    {
        if ($this->request->getParam("id")) {
            $result = $this->pageFactory->create();
            $result->getConfig()->getTitle()->set(
                sprintf(
                    __('Curbside PickUp Order - %s'),
                    $this->request->getParam("id")
                )
            );
            return $result;
        } else {
            $this->message->addErrorMessage(
                __("Invalid curbside pickup order.")
            );
            $this->redirect->redirect($this->response, "*/*/index");
            return $this->response;
        }
    }
}
