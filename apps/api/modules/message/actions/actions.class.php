<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * message api actions.
 *
 * @package    OpenPNE
 * @subpackage opMessagePlugin
 * @author     tatsuya ichikawa <ichikawa@tejimaya.com>
 */
class messageActions extends opJsonApiActions
{
  public function preExecute()
  {
    $this->member = $this->getUser()->getMember();
  }

  public function executePost(sfWebRequest $request)
  {
    $this->forward400If('' === (string)$request['body'], 'body parameter is not specified.');
    $this->forward400If('' === (string)$request['toMember'], 'toMember parameter is not specified.');

    $body = $request['body'];
    $this->myMember = $this->member;
    $toMember = Doctrine::getTable('Member')->find($request['toMember']);
    $this->forward400Unless($toMember, 'invalid member');

    $relation = Doctrine_Core::getTable('MemberRelationship')->retrieveByFromAndTo($toMember->getId(), $this->member->getId());
    $this->forward400If($relation && $relation->getIsAccessBlock(), 'Cannot send the message.');

    $file = $request->getFiles('message_image');
    if ($file)
    {
      try
      {
        // file validation.
        $validator = new opValidatorImageFile(array('required' => false));
        $clean = $validator->clean($file);
      }
      catch (Exception $e)
      {
        $this->logMessage($e->getMessage());

        $this->forward400('This image file is invalid.');
      }
    }

    $conn = Doctrine_Core::getTable('SendMessageData')->getConnection();
    $conn->beginTransaction();

    try
    {
      // try save.
      $message = Doctrine::getTable('SendMessageData')->sendMessage($toMember, SendMessageData::SMARTPHONE_SUBJECT, $body, array(), $conn);

      // if has not file, $clean is null.
      if (!is_null($clean))
      {
        $file = new File();
        $file->setFromValidatedFile($clean);
        $file->save($conn);

        $messageFile = new MessageFile();
        $messageFile->setMessageId($message->getId());
        $messageFile->setFile($file);
        $messageFile->save($conn);
      }

      $conn->commit();
    }
    catch (Exception $e)
    {
      $conn->rollback();
      $this->logMessage($e->getMessage());

      $this->forward400('This massage can not save.');
    }

    return $this->renderJson(array('status' => 'success'));
  }

  public function executeChain(sfWebRequest $request)
  {
    $this->forward400If('' === (string)$request['memberId'], 'memberId parameter is not specified.');
    $this->forward400If('' === (string)$request['maxId'], 'maxId parameter is not specified.');

    $this->pager = Doctrine_Core::getTable('MessageSendList')->getMemberMessagesPager(
      $request['memberId'],
      $this->getUser()->getMemberId(),
      (bool) $request['isAddLow'],
      $request['maxId'],
      25,
      true
    );
  }

  public function executeReadedList(sfWebRequest $request)
  {
    $this->forward400Unless($request['memberId'], 'memberId parameter is not specified.');
    $this->forward400Unless($request['maxUpdatedAt'], 'maxUpdatedAt parameter is not specified.');

    return $this->renderJson(Doctrine_Core::getTable('MessageSendList')->getReadedMessageIds(
      $request['memberId'],
      $this->getUser()->getMemberId(),
      $request['maxUpdatedAt']
    ));
  }

  public function executeRecentList(sfWebRequest $request)
  {
    $keyId = (int) $request->getParameter('keyId', 0);
    $page = (int) $request->getParameter('page', 1);
    $memberIds = opMessagePluginUtil::getMemberIdListFromString($request->getParameter('memberIds'));

    $this->pager = Doctrine_Core::getTable('MessageSendList')->getRecentMessagePager($memberIds, $this->getUser()->getMemberId(), $keyId, $page);
  }
}
