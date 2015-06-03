<?php
namespace NABVelocity;

class Transaction extends AbstractApi {

    /**
     * Authorize
     */
    public function authorize ($workflowId) {}

    /**
     * Authorize and Capture
     */
    public function authorizeAndCapture ($workflowId) {}

    /**
     * Adjust
     *
     * @velocityTansactionType BCP
     * @velocityTansactionType SVA
     * @velocityHttpMethod     PUT
     * @velocityUmp            true
     */
    public function adjust ($workflowId, $transactionId) {}

    /**
     * Undo
     *
     * @velocityTransactionType BCP
     * @velocityTransactionType SVA
     * @velocityTransactionType ECK
     * @velocityHttpMethod      PUT
     * @velocityUmp             true
     */
    public function undo ($workflowId, $transactionId) {}

    /**
     * Capture
     *
     * @velocityTansactionType BCP
     * @velocityTansactionType SVA
     * @velocityHttpMethod     PUT
     * @velocityUmp            true
     */
    public function capture ($workflowId, $transactionId) {}

    /**
     * Capture
     *
     * @velocityTansactionType BCP
     * @velocityHttpMethod     PUT
     * @velocityUmp            true
     */
    public function captureSelective ($workflowId, $transactionIds) {}

    /**
     * Capture
     *
     * @velocityTansactionType BCP
     * @velocityTansactionType ECK
     * @velocityHttpMethod     PUT
     * @velocityUmp            true
     */
    public function captureAll ($workflowId) {}

    /**
     * Capture
     *
     * @velocityTansactionType BCP
     * @velocityHttpMethod     PUT
     * @velocityUmp            true
     */
    public function captureSelectiveAsync ($workflowId, $transactionIds) {}

    /**
     * Capture
     *
     * @velocityTansactionType BCP
     * @velocityHttpMethod     PUT
     * @velocityUmp            true
     */
    public function captureAllAsync ($workflowId, $transactionId) {}

    /**
     * Return by Id
     *
     * @velocityTansactionType BCP
     * @velocityTansactionType SVA
     * @velocityHttpMethod     PUT
     * @velocityUmp            true
     */
    public function returnById ($workflowId) {}

    /**
     * Return Unlinked
     *
     * @velocityTansactionType BCP
     * @velocityTansactionType SVA
     * @velocityHttpMethod     PUT
     * @velocityUmp            true
     */
    public function returnUnlinked ($workflowId) {}

}
