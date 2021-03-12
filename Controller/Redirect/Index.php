<?php

namespace Avarda\Payments\Controller\Redirect;

use Avarda\Payments\Helper\AuthorizationStatus;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /** @var UrlInterface */
    protected $urlBuilder;

    /** @var Session */
    protected $session;

    /** @var TransactionRepositoryInterface */
    protected $transactionRepository;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var OrderSender */
    protected $orderSender;

    /** @var FilterBuilder */
    protected $filterBuilder;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var AuthorizationStatus */
    protected $authorizationStatus;

    /** @var InvoiceService */
    protected $invoiceService;

    /** @var TransactionFactory */
    protected $transactionFactory;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        Context $context,
        Session $session,
        TransactionRepositoryInterface $transactionRepository,
        OrderRepositoryInterface $orderRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderSender $orderSender,
        AuthorizationStatus $authorizationStatus,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->urlBuilder = $context->getUrl();
        $this->session = $session;
        $this->transactionRepository = $transactionRepository;
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->authorizationStatus = $authorizationStatus;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // Check order number
        $authorizationId = $this->getRequest()->getParam('authorizationId');
        if (empty($authorizationId)) {
            $this->session->restoreQuote();
            $this->messageManager->addErrorMessage(__('Invalid request'));
            $this->_redirect('checkout/cart');
            return;
        }

        $filter = $this->filterBuilder->create()
            ->setField('avarda_payments_authorize_id')
            ->setValue($authorizationId)
            ->setConditionType('eq');
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$filter])
            ->create();

        $orders = $this->orderRepository->getList($searchCriteria);
        if (!$orders->getTotalCount()) {
            $this->session->restoreQuote();
            $this->messageManager->addErrorMessage(__('No order for processing found'));
            $this->_redirect('checkout/cart');
            return;
        }

        $orderList = $orders->getItems();
        $order = reset($orderList);

        /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
        $method = $order->getPayment()->getMethodInstance();

        // Unset session variables
        $this->session->unsAvardaPaymentsRedirectUrl();
        $this->session->unsAvardaPaymentsAuthorizeId();

        $this->authorizationStatus->updateOrderStatus($order);
        $additionalInformation = $order->getPayment()->getAdditionalInformation();

        if (!isset($additionalInformation['avarda_payments_status']) ||
            $additionalInformation['avarda_payments_status'] != AuthorizationStatus::STATE_APPROVED) {
            // Cancel order
            $order->cancel();
            $order->addCommentToStatusHistory(__('Order canceled. %1.', [$this->authorizationStatus->getStateText($additionalInformation['avarda_payments_status'])]));
            $order->save();

            // Restore the quote
            $this->session->restoreQuote();

            $this->messageManager->addErrorMessage(__('Failed to complete the payment. Please try again or contact the customer service.'));

            $this->_redirect('checkout/cart');

            return;
        }

        // Change order status
        $newStatus = $method->getConfigData('order_status');
        $order->setStatus($newStatus);
        $order->addCommentToStatusHistory(__('Authorization has been completed'));
        $this->orderRepository->save($order);

        // Send order notification
        try {
            $this->orderSender->send($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        // Redirect to success page
        $this->session->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success');
    }
}
