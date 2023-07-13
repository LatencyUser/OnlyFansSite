/*
* Feed page & component
 */
"use strict";
/* global app, user, streamVars, launchToast, trans, Pusher, PusherBatchAuthorizer, pusher, videojs, updateButtonState */

$(function () {

    if(streamVars.canWatchStream){
        Stream.initVideo();
    }

    //TODO: Only init these if necessary
    Stream.initPusher();
    Stream.presenceChannelConnect(streamVars.streamId);
    Stream.chatChannelConnect(streamVars.streamId);

    // Chat related
    Stream.initAutoScroll();
    Stream.scrollChatToBotton();
    if(streamVars.streamOwnerId === user.user_id) {
        Stream.showChatMessageActions();
    }

    Stream.initStreamPaymentButtons();
});

var Stream = {

    pusher: null,
    streamComments:{},

    /**
     * Instantiates the video player
     */
    initVideo: function(){
        var player = videojs('my_video_1',{
            plugins: {
                httpSourceSelector:
                        {
                            default: 'auto'
                        }
            },
            autoplay: true,
            preload: "auto",
            controls: true,
            poster: streamVars.streamPoster,
            controlBar: {
                pictureInPictureToggle: false
            }

        },
            // function onPlayerReady() {
            //     videojs.log('Your player is ready!');
            //
            //     // In this context, `this` is the player that was created by Video.js.
            //     this.play();
            //
            //     // // How about an event listener?
            //     // this.on('ended', function() {
            //     //     videojs.log('Awww...over so soon?!');
            //     // });
            //     // this.hlsQualitySelector({ displayCurrentQuality: true });
            // }
        );

        player.httpSourceSelector();
        player.play();
    },

    /**
     * Instantiates the pusher connection
     */
    initPusher: function(){
        Pusher.logToConsole = typeof streamVars.pusherDebug !== 'undefined' ? streamVars.pusherDebug : false;
        Stream.pusher = new Pusher(pusher.key, {
            authorizer: PusherBatchAuthorizer,
            authDelay: 200,
            cluster: streamVars.pusherCluster,
            forceTLS: true,
            authEndpoint:  app.baseUrl + '/authorizeStreamPresence',
            auth: {
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            }
        });
    },

    /**
     * Instantiates the payments buttons
     */
    initStreamPaymentButtons:function(){
        $('.stream-subscribe-label').on('click', function () {
            $('.stream-subscribe-button').click();
        });
        $('.stream-unlock-label').on('click', function () {
            $('.stream-unlock-button').click();
        });
    },

    /**
     * Connecting to the live count presence channel
     * @param streamId
     */
    presenceChannelConnect: function (streamId) {
        var presenceChannel = Stream.pusher.subscribe("presence-stream-"+streamId);
        presenceChannel.bind('pusher:subscription_succeeded', function(members) {
            Stream.setLiveUsersCount(members.count);
        });
        // eslint-disable-next-line no-unused-vars
        presenceChannel.bind('pusher:member_added', function(member) {
            Stream.setLiveUsersCount(presenceChannel.members.count);
        });

        // eslint-disable-next-line no-unused-vars
        presenceChannel.bind('pusher:member_removed', function(member) {
            Stream.setLiveUsersCount(presenceChannel.members.count);
        });
    },

    /**
     * Stream chat connection method
     * @param streamId
     */
    chatChannelConnect: function(streamId){
        let channel = Stream.pusher.subscribe('private-stream-chat-channel-'+streamId);
        channel.bind('new-message', function(data) {
            if(data.userId !== user.user_id){
                Stream.appendCommentToStreamChat(data.message);
                Stream.updateChatNoCommentsLabel();
            }
        });
    },


    /**
     * Updates the UI with new live users count
     * @param val
     */
    setLiveUsersCount: function (val) {
        $('.live-stream-users-count').html(val);
    },

    /**
     * Sends stream chat message
     * @param streamId
     * @returns {boolean}
     */
    sendMessage: function(streamId) {
        updateButtonState('loading',$('.send-message'));
        if($('.messageBoxInput').val().length === 0){
            $('.messageBoxInput').addClass('is-invalid');
            updateButtonState('loaded',$('.send-message'));
            return false;
        }else{
            $('.messageBoxInput').removeClass('is-invalid');
        }
        $.ajax({
            type: 'POST',
            url: app.baseUrl + '/stream/comments/add',
            data: {
                'message': $('.conversation-writeup .messageBoxInput').val(),
                'streamId' : streamId
            },
            dataType: 'json',
            success: function (result) {
                Stream.appendCommentToStreamChat(result.dataHtml);
                Stream.updateChatNoCommentsLabel();
                updateButtonState('loaded',$('.send-message'));
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Toggles chat state between "messageless" or not
     */
    updateChatNoCommentsLabel: function(){
        if($('.conversation-content .stream-chat-message').length){
            $('.no-chat-comments-label').removeClass('d-flex');
            $('.no-chat-comments-label').addClass('d-none');
        }
        else{
            $('.no-chat-comments-label').removeClass('d-none');
            $('.no-chat-comments-label').addClass('d-flex');
        }
    },

    /**
     * Adds message to stream chat
     * @param comment
     */
    appendCommentToStreamChat: function(comment){
        $('.conversation-content').append(comment);
        $('.messageBoxInput').val('');
        if(streamVars.streamOwnerId === user.user_id) {
            Stream.showChatMessageActions();
        }
        Stream.scrollChatToBotton();
    },

    /**
     * Keeps chat scrolled to bottom
     */
    scrollChatToBotton: function () {
        if($('.conversation-content .stream-chat-message').length){
            $(".conversation-content").animate({ scrollTop: $('.conversation-content')[0].scrollHeight}, 800);
        }
    },

    /**
     * Inits the autoscroll method
     */
    initAutoScroll: function(){
        $(".messageBoxInput").keydown(function(e){
            // Enter was pressed without shift key
            if (e.keyCode === 13)
            {
                if(!e.shiftKey){
                    e.preventDefault();
                    $('.send-message').trigger('click');
                }
            }
        });
    },

    /**
     * Toggles the delete comment button visiblity
     */
    showChatMessageActions: function(){
        $('.chat-message-action').removeClass('d-none');
    },

    /**
     * Deletes users comments (as admin)
     * @param commentId
     */
    deleteComment: function (commentId) {
        if(confirm(trans("Are you sure you want to delete this comment?"))){
            $.ajax({
                type: 'DELETE',
                data: {
                    'id': commentId
                },
                dataType: 'json',
                url: app.baseUrl+'/stream/comments/delete',
                success: function () {
                    let element = $('*[data-commentid="'+commentId+'"]');
                    element.remove();
                    Stream.updateChatNoCommentsLabel();
                    launchToast('success',trans('Success'),trans('Comment removed'));
                },
                error: function (result) {
                    launchToast('danger',trans('Error'),result.responseJSON.message);
                }
            });
        }
    },

};
