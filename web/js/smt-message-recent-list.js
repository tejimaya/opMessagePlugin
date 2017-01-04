'use strict';
$(document).ready(function() {

  var message = {

    /**
     * message template.
     */
    $template: $('#message-template'),

    /**
     * config.
     */
    config: {

      /**
       * heartbeat timer.
       */
      timer: null,

      /**
       * interval (second).
       */
      interval: 5,

      /**
       * heartbeat function.
       */
      heartbeatTarget: null
    },

    /**
     * initialize.
     */
    initialize: function() {

      // set baseUrl
      if (typeof $('#baseUrl').val() == 'string') {
        openpne.baseUrl = $('#baseUrl').val();
      }
      openpne.baseUrl += '/';

      // common.
      $('.message-created-at').timeago();

      // for message/receiveList page.
      if ($('body').is('#page_message_smtList')) {
        this.config.heartbeatTarget = this.updateNewRecentList.bind(false);

        this.updateNewRecentList(true).always(function() {
          // set timer.
          message.startHeartbeatTimer();
        });

        $('#messagePrevLink').click(function() {
          message.clickPrevButton();
        });

        $('#messageNextLink').click(function() {
          message.clickNextButton();
        });
      }
    },

    /**
     * click #messagePrevLink id button.
     */
    clickPrevButton: function() {
      var prevPage = Number($('#prevPage').val());

      this.clickPagerLink(prevPage);
    },

    /**
     * click #messageNextLink id button.
     */
    clickNextButton: function() {
      var nextPage = Number($('#nextPage').val());

      this.clickPagerLink(nextPage);
    },

    clickPagerLink: function(page) {

      if (isNaN(page) || page === 0) {
        return;
      }

      this.hidePager();
      $('#message-wrapper-parent').find('.message-wrapper').remove();
      $('#messageKeyId').val(0);
      $('#memberIds').val('');

      $('#first-loading').show();
      this.stopHeartbeatTimer();

      $('#page').val(page);

      this.updateNewRecentList(true).always(function() {
        // set timer.
        message.startHeartbeatTimer();
      });
    },

    /**
     * update recent list message data.
     * @param datas
     */
    updateRecentListMessageTemplate: function(datas) {
      var maxId = Number($('#messageKeyId').val());

      if (isNaN(maxId)) {
        maxId = 0;
      }

      $(datas).each(function(i, data) {
        var
          template = message.$template.children().clone(),
          $oldHtml = $('div[data-member-id="' + data.member.id + '"]');

        if ($oldHtml.is('.message-wrapper')) {
          $oldHtml.remove();
        }

        if (maxId < data.id) {
          maxId = data.id;
        }

        template
          .attr('data-member-id', data.member.id)
          .addClass('show')
            .find('.memberIcon')
            .append('<a href="' + data.member.profile_url + '"><img src="' + data.member.profile_image + '" /></a>')
          .end()
            .find('.memberProfile')
            .append('<a href="' + data.member.profile_url + '">' + data.member.name + '</a>')
          .end()
            .find('.lastMessage')
            .append('<a href="' + openpne.baseUrl + 'message/smtChain?id=' + data.member.id + '">' + data.summary + '</a>')
          .end()
            .find('.message-created-at')
            .attr('title', data.created_at)
          .end();

        if (typeof data.is_read == 'boolean' && !data.is_read) {
          template.addClass('message-unread');
        }

         $('#message-wrapper-parent').prepend(template);
      });

      $('.message-created-at').timeago();
      $('#messageKeyId').val(maxId);
    },

    /**
     * update recent list pagenation.
     */
    updatePager: function(response) {

      $('#page').val(response.page);

      if (response.previousPage) {
        $('#prevPage').val(response.previousPage);
        $('#messagePrevLink').show();
      }

      if (response.nextPage) {
        $('#nextPage').val(response.nextPage);
        $('#messageNextLink').show();
      }

      if (response.previousPage || response.nextPage) {
        $('.pager').show();
      }
    },

    /**
     * hide pagenation.
     */
    hidePager: function() {
      $('#messagePrevLink').hide();
      $('#messageNextLink').hide();
      $('.pager').hide();

      $('#nextPage').val('');
      $('#prevPage').val('');
      $('#page').val('');
    },

    /**
     * update Time info line.
     */
    updateTimeInfo: function() {
      var
        timeInfoWrapper = $('#message-wrapper-parent').find('.time-info-wrapper'),
        currentDate,
        baseDate;

      for (var i = 0; i < timeInfoWrapper.length; i++) {
        currentDate = timeInfoWrapper.eq(i).attr('data-created-at-date');
        if (currentDate) {
          if (currentDate === baseDate) {
            timeInfoWrapper.eq(i).hide();
          } else {
            timeInfoWrapper.eq(i).show();
          }

          baseDate = currentDate;
        }
      }
    },

    /**
     * start heartbeat timer.
     */
    startHeartbeatTimer: function() {
      this.config.timer = setTimeout(this.config.heartbeatTarget, this.config.interval * 1000);
    },

    /**
     * stop heartbeat timer.
     */
    stopHeartbeatTimer: function() {
      clearTimeout(this.config.timer);
    },

    /**
     * insert Message template by data.
     * @param keyId
     */
    getRecentList: function(keyId, page, memberIds) {

      var dfd = $.Deferred();

      $.ajax({
        url: openpne.apiBase + "message/recentList.json",
        type: 'POST',
        data: {
          apiKey: openpne.apiKey,
          keyId: Number(keyId),
          page: Number(page),
          memberIds: memberIds,
        },
        dataType: 'json',
        success: function(response) {
          dfd.resolve(response);
        },
        error: function(e) {
          dfd.reject();
        }
      });

      return dfd.promise();
    },

    /**
     * update new recent list.
     */
    updateNewRecentList: function(notUseHeartbeat) {

      var
        keyId = Number($('#messageKeyId').val()),
        page = Number($('#page').val()),
        memberIds = $('#memberIds').val(),
        dfd = $.Deferred();

      if (isNaN(keyId)) {
        keyId = 0;
      }

      if (isNaN(page)) {
        page = 1;
      }

      message.getRecentList(keyId, page, memberIds).done(function(response) {

        $('#first-loading').hide();

        if (response.memberIds.length) {
          $('#memberIds').val(response.memberIds);
        }

        message.updatePager(response);
        message.updateRecentListMessageTemplate(response.data);
        dfd.resolve();

        if (!$('#message-wrapper-parent').find('.message-wrapper').length) {
          $('#no-message').show();

          return false;
        }

        $('#no-message').hide();

      }).always(function() {

        if (!notUseHeartbeat) {
          message.startHeartbeatTimer();
        }

      }).fail(function() {
        dfd.reject();
        // TODO error design.
      });

      return dfd.promise();
    }
  };

  message.initialize();
});
