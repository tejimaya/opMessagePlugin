<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class updateOpMessagePlugin_2_0_0_3 extends opMigration
{
  public function up()
  {
    $this->changeColumn('deleted_message', 'message_id', 'integer', 4, array('notnull' => 0));
    $this->changeColumn('deleted_message', 'message_send_list_id', 'integer', 4, array('notnull' => 0));
  }
}
