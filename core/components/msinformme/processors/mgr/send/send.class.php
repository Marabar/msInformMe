<?php

class msInformMeSendProcessor extends modProcessor {
    public $classKey = 'msProductData';
    /** @var msInformMe $msInformMe */
    public $msInformMe;


    /**
     * @return bool
     */
    public function initialize()
    {
        $this->msInformMe = $this->modx->getService('msInformMe');
        return parent::initialize(); // TODO: Change the autogenerated stub
    }


    /**
     * @return array|mixed|string
     */
    public function process() {
        $id = $this->getProperty('id');
        if (!$email = $this->getProperty('email')) {
            return $this->failure($this->modx->lexicon('msinformme_err_not_email'));
        }
        $product = $this->msInformMe->getProduct($id);
        $user = $this->msInformMe->getUser($email);
        $scriptProperties = array_merge($user, $product);

        $subject = $this->modx->getOption('msinformme_email_subject');
        if ($templateId = $this->modx->getOption('msinformme_template_send')) {
            if (!$body = $this->msInformMe->getTemplate($templateId, $scriptProperties)) {
                return $this->failure($this->modx->lexicon('msinformme_err_no_template'));
            }
        } else {
            return $this->failure($this->modx->lexicon('msinformme_err_template_send'));
        }

        $send = [];
        $send['from'] = $this->modx->getOption('msinformme_email_sender');
        $send['from_name'] = $this->modx->getOption('site_name');
        $send['subject'] = !empty($subject)
            ? $subject
            : $this->modx->lexicon('msinformme_message_from') . $send['from_name'];
        $send['email'] = $email;
        $send['replyTo'] = $this->modx->getOption('msinformme_email_reply_to');
        $send['template'] = $body;

        $result = $this->msInformMe->sendEmail($send);

        if (!isset($result['result'])) {
            return $this->success($this->modx->lexicon('msinformme_send_success'));
        } else {
            return $this->failure($result['messages']);
        }
    }
}

return 'msInformMeSendProcessor';