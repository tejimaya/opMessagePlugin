<?php
use_helper('opMessage');
$data = array();

$readedMaxUpdatedAt = null;
foreach ($pager->getResults() as $messageList)
{
  $data[] = op_api_message($messageList, $messageList->getSendFrom(), true);
  $updatedAt = $messageList->getUpdatedAt();
  if ($messageList->getIsRead() && (is_null($readedMaxUpdatedAt) || $readedMaxUpdatedAt < $updatedAt))
  {
    $readedMaxUpdatedAt = $updatedAt;
  }
}

return array(
  'status' => 'success',
  'data' => $data,
  'readed_max_updated_at' => $readedMaxUpdatedAt,
  'has_more' => $pager->hasOlderPage(),
);
