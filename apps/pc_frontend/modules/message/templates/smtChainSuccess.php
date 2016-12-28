<?php use_helper('opAsset', 'Javascript', 'opMessage') ?>
<?php op_smt_use_stylesheet('/opMessagePlugin/css/smt-message.css?2.0.0alpha2', sfWebResponse::LAST) ?>
<?php op_smt_use_stylesheet('/opMessagePlugin/css/bootstrap-popover.css', sfWebResponse::LAST) ?>
<?php op_smt_use_javascript('/opMessagePlugin/js/jquery.timeago.js', sfWebResponse::LAST) ?>
<?php op_smt_use_javascript('/opMessagePlugin/js/smt-message.js', sfWebResponse::LAST) ?>
<?php op_smt_use_javascript('/opMessagePlugin/js/bootstrap.min.js', sfWebResponse::LAST) ?>
<input type="hidden" value="<?php echo $member->getId() ?>" name="toMember" id="messageToMember" />
<input type="hidden" value="<?php echo sfConfig::get('op_base_url') ?>" name="baseUrl" id="baseUrl" />

<div class="row" id="message-member">
  <div class="content">
    <div class="row">
      <div class="gadget_header span12"><?php echo $member->getname() ?></div>
    </div>
    <div class="row" id="member-image">
      <div class="span3 center">
        <?php echo op_link_to_member($member, array('link_target' => op_image_tag_sf_image($member->getImageFileName(), array('size' => '48x48')))) ?>
        <div class="row face-name"><?php echo op_link_to_member($member) ?></div>
      </div>
      <div class="span6">&nbsp;</div>
      <div class="span3 center">
        <?php $myMember = $sf_user->getMember() ?>
        <?php echo op_link_to_member($myMember, array('link_target' => op_image_tag_sf_image($myMember->getImageFileName(), array('size' => '48x48')))) ?>
        <div class="row face-name"><?php echo $myMember->getName() ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div id="loading-more" class="center" style="display: none;">
    <?php echo op_image_tag('ajax-loader.gif');?>
  </div>
  <div id="more" class="btn span12" style="display: none;"><?php echo __('More') ?></div>
</div>

<div id="message-wrapper-parent">
  <p id="no-message" style="display: none;"><?php echo __('There are no messages') ?></p>
  <div id="first-loading" class="center">
    <?php echo op_image_tag('ajax-loader.gif');?>
  </div>
</div>

<div class="row">
  <div id="loading" class="center" style="display: none;">
    <?php echo op_image_tag('ajax-loader.gif');?>
  </div>
</div>

<?php if (!$isBlocked): ?>
<div id="submit-wrapper" class="row">
  <div class="span9">
    <form enctype="multipart/form-data" method="post" id="send-message-form">
      <input type="file" name="message_image" id="message_image" />
      <textarea name="body" id="submit-message"></textarea>
    </form>
  </div>
  <div class="span3">
    <button id="do-submit" class="btn btn-primary"><?php echo __('Send') ?></button>
  </div>
</div>
<?php endif ?>

<div id="message-template" style="display: none;">
  <div class="time-info-wrapper" style="display: none;">
    <p class="time-info"><i class="icon-time"></i></p>
  </div>
  <div class="timeago"><p class="message-created-at"></p></div>
  <div class="message-wrapper row popover">
    <div class="arrow"></div>
    <h3 class="popover-title"></h3>
    <div class="popover-content">
      <div class="body">
        <p class="message-body"></p>
        <ul class="photo"></ul>
      </div>
    </div>
  </div>
</div>
