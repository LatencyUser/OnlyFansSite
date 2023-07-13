/**
 *
 * Messages Component
 *
 */
"use strict";
/* global app, user, messengerVars, pusher, FileUpload,
  Lists, Pusher, PusherBatchAuthorizer, updateButtonState,
  mswpScanPage, trans, bootstrapDetectBreakpoint, incrementNotificationsCount,
  EmojiButton, filterXSS, launchToast, initTooltips, soketi, socketsDriver */

$(function () {

    if(messengerVars.bootFullMessenger){
        messenger.boot();
        messenger.fetchContacts();
        messenger.initAutoScroll();
        messenger.initMarkAsSeen();
        messenger.resetTextAreaHeight();
        messenger.initEmojiPicker();
        if(messengerVars.lastContactID !== false && messengerVars.lastContactID !== 0){
            messenger.fetchConversation(messengerVars.lastContactID);
        }
        FileUpload.initDropZone('.dropzone','/attachment/upload/message');
        messenger.initSelectizeUserList();
    }

});

var messenger = {

    state : {
        contacts:[],
        conversation:[],
        activeConversationUserID:null,
        activeConversationUser:null,
        currentBreakPoint: 'lg',
        redirectedToMessage: false,
        // Used for disabling new message dialog box if no contacts are available
        fetchedContactsListsCount: 0,
        hasAvailableFetchedContacts: true,
        messagePrice: 5,
        isPaidMessage: false,
    },

    pusher: null,

    /**
     * Boots up the main messenger functions
     */
    boot: function(){
        Pusher.logToConsole = typeof messengerVars.pusherDebug !== 'undefined' ? messengerVars.pusherDebug : false;
        let params = {
            authorizer: PusherBatchAuthorizer,
            authDelay: 200,
            authEndpoint: app.baseUrl + '/my/messenger/authorizeUser',
            auth: {
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            }
        };
        if(socketsDriver === 'soketi'){
            params.wsHost = soketi.host;
            params.wsPort = soketi.port;
        }
        else{
            params.cluster = messengerVars.pusherCluster;
        }
        messenger.pusher = new Pusher(socketsDriver === 'soketi' ? soketi.key : pusher.key, params);
    },

    /**
     * Instantiates pusher sockets for each conversation (batched)
     */
    initLiveSockets: function(){
        $.each(messenger.state.contacts, function (k,v) {
            const minID = Math.min(v.receiverID,v.senderID);
            const maxID = Math.max(v.receiverID,v.senderID);
            const keyID = ("" + minID + '-' + maxID);
            let channel = messenger.pusher.subscribe('private-chat-channel-'+keyID);
            channel.bind('new-message', function(data) {
                const message = jQuery.parseJSON(data.message);
                if(message.sender_id === messenger.state.activeConversationUserID){
                    messenger.state.conversation.push(message);
                    messenger.reloadConversation();
                }
                messenger.updateUnreadMessagesCount(parseInt($('#unseenMessages').html()) + 1);
                messenger.addLatestMessageToConversation(message.sender_id,message);
                messenger.markConversationAsRead(message.sender_id,'unread');
                messenger.reloadContactsList();
                messenger.setActiveContact(messenger.state.activeConversationUserID);
            });
        });
    },

    /**
     * Initiate chatbox scroll to bottom event
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
     * Fetches all messenger contacts
     */
    fetchContacts: function () {
        $.ajax({
            type: 'GET',
            url: app.baseUrl + '/my/messenger/fetchContacts',
            dataType: 'json',
            success: function (result) {
                if(result.status === 'success'){
                    messenger.state.contacts = result.data.contacts;
                    messenger.reloadContactsList();
                    messenger.initLiveSockets();
                }
                else{
                    // messenger.state.contacts = result.data
                }
            }
        });
    },

    /**
     * Switches between layout having horiznatal scroll for contacts or not
     */
    makeContactsHeaderResponsive: function(){
        const breakPoint = bootstrapDetectBreakpoint();
        if(breakPoint.name === 'xs'){
            $('.conversations-list').mCustomScrollbar({
                theme: "minimal-dark",
                axis:'x',
                scrollInertia: 200,
            });
            $('.conversations-list').addClass('border-top');
        }
        else{
            $('.conversations-list').mCustomScrollbar("destroy");
            $('.conversations-list').removeClass('border-top');
        }
    },

    /**
     * Fetches conversation with certain user
     * @param userID
     */
    fetchConversation: function (userID) {

        // Setting up loading and clearign up conv content
        $('.conversation-loading-box').removeClass('d-none');
        $('.conversation-header-loading-box').removeClass('d-none');
        $('.conversation-header').addClass('d-none');

        // Setting up loading and clearign up conv content
        $('.conversation-loading-box').removeClass('d-none');
        $('.conversation-content').html('');
        $.ajax({
            type: 'GET',
            url: app.baseUrl + '/my/messenger/fetchMessages/' + userID,
            dataType: 'json',
            success: function (result) {
                if(result.status === 'success'){
                    messenger.state.conversation = result.data.messages;
                    messenger.reloadConversation();
                    messenger.state.activeConversationUserID = userID;
                    messenger.setActiveContact(userID);
                    messenger.reloadConversationHeader();
                    initTooltips();
                }
                else{
                    // messenger.state.contacts = result.data
                }
            }
        });
    },

    /**
     * Sends the message
     * @returns {boolean}
     */
    sendMessage: function(forceSave = false) {

        // Checking if files are being uploaded
        if(FileUpload.isLoading === true && forceSave === false){
            $('.confirm-post-save').unbind('click');
            $('.confirm-post-save').on('click',function () {
                messenger.sendMessage(true);
            });
            $('#confirm-post-save').modal('show');
            return false;
        }

        // Check if locked message has at least one attachment
        if(messenger.state.isPaidMessage && FileUpload.attachaments.length === 0){
            $('#no-attachments-locked-post').modal('show');
            return false;
        }

        updateButtonState('loading',$('.send-message'));
        // Validation
        if($('.messageBoxInput').val().length === 0 && FileUpload.attachaments.length === 0){
            updateButtonState('loaded',$('.send-message'));
            return false;
        }
        $.ajax({
            type: 'POST',
            url: app.baseUrl + '/my/messenger/sendMessage',
            data: {
                'message': $('.conversation-writeup .messageBoxInput').val(),
                'attachments' : FileUpload.attachaments,
                'receiverID' : $('.conversation-writeup #receiverID').val(),
                'price': messenger.state.isPaidMessage ? messenger.state.messagePrice : 0
            },
            dataType: 'json',
            success: function (result) {
                messenger.state.conversation.push(result.data.message);
                messenger.reloadConversation();
                messenger.clearMessageBox();
                messenger.addLatestMessageToConversation(result.data.message.receiverID,result.data.message);
                messenger.reloadContactsList();
                messenger.hideEmptyChatElements();
                messenger.clearFileUploadsState();
                messenger.resetTextAreaHeight();
                messenger.clearMessagePrice();
                updateButtonState('loaded', $('.send-message'));
                $('#confirm-post-save').modal('hide');
                initTooltips();
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Clears up uploaded files
     */
    clearFileUploadsState: function(){
        FileUpload.attachaments = [];
        $('.dropzone-previews').html('');
    },

    /**
     * Creates initial (new) conversation
     * @returns {boolean}
     */
    createConversation: function() {
        let data = $("#userMessageForm").serialize()+'&new=true';

        if($('#userMessageForm #select-repo').val() === ""){
            $('#userMessageForm .mfv-errorBox').html('<div class="alert alert-dismissable alert-danger text-white">\
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">						<span aria-hidden="true">&times;</span>					</button>'
                +trans('Please select an user first')+'. </div>');
            return false;
        }

        if($('#userMessageForm #messageText').val() === ""){
            $('#userMessageForm .mfv-errorBox').html('<div class="alert alert-dismissable alert-danger text-white">\
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">						<span aria-hidden="true">&times;</span>					</button>'
                +trans('Please enter your message')+'.</div>');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: app.baseUrl + '/my/messenger/sendMessage',
            data: data,
            success: function (result) {
                $("textarea[name=message]").val("");
                $('#messageModal').modal('hide');
                let contactID = result.data.contact[0].contactID;
                if(!messenger.isExistingContact(contactID)){
                    messenger.state.contacts.unshift(result.data.contact[0]);
                }
                else{
                    // add latest contact details
                    $.map(messenger.state.contacts,function (contact,k) {
                        if(contactID === contact.contactID){
                            let newContact = result.data.contact[0];
                            messenger.state.contacts[k] = newContact;
                        }
                    });
                }
                messenger.reloadContactsList();
                messenger.state.activeConversationUserID = contactID;
                messenger.fetchConversation(contactID);
                messenger.hideEmptyChatElements();
                messenger.initLiveSockets();
                initTooltips();
            }
        });
    },

    /**
     * Method used for starting a conversation from the profile page
     */
    sendDMFromProfilePage: function(){
        let data = $("#userMessageForm").serialize()+'&new=true';
        $.ajax({
            type: 'POST',
            url: app.baseUrl + '/my/messenger/sendMessage',
            data: data,
            success: function () {
                $("textarea[name=message]").val("");
                $('#messageModal').modal('hide');
                window.location.assign(app.baseUrl + '/my/messenger');
            }
        });
    },

    /**
     * Marks message as seen
     */
    initMarkAsSeen:function(){
        $( ".messageBoxInput" ).on('click', function() {
            if($('#unseenValue').val() !== 0){
                $.ajax({
                    type: 'POST',
                    url: app.baseUrl + '/my/messenger/markSeen',
                    data: {userID:messenger.state.activeConversationUserID},
                    dataType: 'json',
                    success: function (result) {
                        messenger.markConversationAsRead(messenger.state.activeConversationUserID,'read');
                        messenger.updateUnreadMessagesCount(parseInt($('#unseenMessages').html()) - result.data.count);
                        incrementNotificationsCount('.menu-notification-badge.chat-menu-count', (-parseInt(result.data.count)));
                        messenger.reloadContactsList();
                    }
                });
            }
        });
    },

    /**
     * Checks if user already has a conversation with certain user
     * @param contactID
     * @returns {boolean}
     */
    isExistingContact: function(contactID){
        // Search if contact is present
        let isNewContact = false;
        $.map(messenger.state.contacts,function (contact) {
            if(contactID === contact.contactID){
                isNewContact = true;
            }
        });
        return isNewContact;
    },

    /**
     * Reloads conversation list
     */
    reloadContactsList: function () {
        let contactsHtml = '';
        $.each( messenger.state.contacts, function( key, value ) {
            contactsHtml += contactElement(value);
        });
        if(messenger.state.contacts.length > 0){
            $('.conversations-list').html('<div class="row">'+contactsHtml+'</div>');
        }
    },

    /**
     * Reloads convesation header
     */
    reloadConversationHeader: function(){
        if(typeof messenger.state.conversation[0] !== 'undefined'){
            const contact = messenger.state.conversation[0];
            const userID = (contact.receiver_id !== messenger.state.activeConversationUserID ? contact.sender.id : contact.receiver.id);
            const username = (contact.receiver_id !== messenger.state.activeConversationUserID ? contact.sender.username : contact.receiver.username);
            const avatar = (contact.receiver_id !== messenger.state.activeConversationUserID ? contact.sender.avatar : contact.receiver.avatar);
            const name = contact.receiver_id !== messenger.state.activeConversationUserID ? `${contact.sender.name} ` : `${contact.receiver.name}`;
            const profile = contact.receiver_id !== messenger.state.activeConversationUserID ? contact.sender.profileUrl : contact.receiver.profileUrl;
            $('.conversation-header').removeClass('d-none');
            $('.conversation-header-loading-box').addClass('d-none');
            $('.conversation-header-avatar').attr('src',avatar);
            $('.conversation-header-user').html(name);
            $('.conversation-profile-link').attr('href',profile);

            $('.details-holder .unfollow-btn').unbind('click');
            $('.details-holder .block-btn').unbind('click');
            $('.details-holder .report-btn').unbind('click');

            $('.details-holder .unfollow-btn').on('click',function () {
                Lists.showListManagementConfirmation('unfollow', userID);
            });
            $('.details-holder .block-btn').on('click',function () {
                Lists.showListManagementConfirmation('block', userID);
            });
            $('.details-holder .report-btn').on('click',function () {
                Lists.showReportBox(userID,null);
            });
            if(contact.sender.canEarnMoney === false) {
                $('.details-holder .tip-btn').addClass('hidden');
            } else {
                $('.details-holder .tip-btn').attr('data-username','@'+username);
                $('.details-holder .tip-btn').attr('data-name',name);
                $('.details-holder .tip-btn').attr('data-avatar',avatar);
                $('.details-holder .tip-btn').attr('data-recipient-id',userID);
            }

        }
    },

    /**
     * Reloads conversation
     */
    reloadConversation: function () {
        let conversationHtml = '';
        $.each( messenger.state.conversation, function( key, value ) {
            conversationHtml += messageElement(value);
        });
        $('.conversation-content').html(conversationHtml);

        // Navigating to last message or last paid mesage
        let urlParams = new URLSearchParams(window.location.search);
        // Scrolling to newly unlocked message if this redirect comes from a message-unlock payment
        if(urlParams.has('token') && !messenger.state.redirectedToMessage) {
            let token = '#m-'.concat(urlParams.get('token'));
            if($('.conversation-content .message-box').length && $('.conversation-content').find(token).length){
                let offset = $('.conversation-content').find(token).offset().top - $('.conversation-content').offset().top + $('.conversation-content').scrollTop();
                $(".conversation-content").animate({scrollTop: offset}, 'slow');
            }

            $('.conversation-content').find(token).animate({
                backgroundColor: "rgba(203,12,159,.2)",
            }, 1000).delay(2000).queue(function() {
                $('.conversation-content').find(token).animate({
                    backgroundColor: "rgba(0,0,0,0)",
                }, 1000).dequeue();
            });

            messenger.state.redirectedToMessage = true;
        } else {
            // Scrolling down to last message
            if($('.conversation-content .message-box').length){
                $(".conversation-content").animate({ scrollTop: $('.conversation-content')[0].scrollHeight + 100}, 800);
            }
        }
        $('.conversation-loading-box').addClass('d-none');
        messenger.initLinks();
        messenger.initMessengerGalleries();
    },

    /**
     * Method used for auto adjusting textarea message height on resize
     * @param el
     */
    textAreaAdjust: function(el) {
        el.style.height = (el.scrollHeight > el.clientHeight) ? (el.scrollHeight)+"px" : "40px";
    },

    /**
     * Resets the send new message text area height
     */
    resetTextAreaHeight: function(){
        $(".messageBoxInput").css('height',45);
    },

    /**
     * Set currently active contact
     * @param userID
     */
    setActiveContact: function (userID) {
        $('.messageBoxInput').focus();
        $('#receiverID').val(userID);
        $('.contact-box').each(function (k,el) {
            $(el).removeClass('contact-active');
        });

        setTimeout(function(){ $('.contact-'+userID).addClass('contact-active'); }, 100);

    },

    /**
     * Clears up the new message field
     */
    clearMessageBox: function(){
        $(".messageBoxInput").val('');
    },

    /**
     * Updates the unread messages count
     * @param val
     * @returns {boolean}
     */
    updateUnreadMessagesCount: function (val) {
        $("#unseenMessages").html(val);
        return true;
    },

    /**
     * Marks conversation as being read
     * @param userID
     * @param type
     */
    markConversationAsRead: function (userID, type) {
        $.map(messenger.state.contacts,function (contact,k) {
            if(userID === contact.contactID){
                let newContact = contact;
                newContact.isSeen = type === 'read' ? 1 : 0;
                messenger.state.contacts[k] = newContact;
            }
        });
        // eslint-disable-next-line no-unused-vars
        let newContactsList = messenger.state.contacts; // These kinds of stuff should be immutable
    },

    /**
     * Appends latest message to the conversation
     * @param contactID
     * @param message
     */
    addLatestMessageToConversation: function (contactID, message) {
        // add latest contact details
        let contactKey = null;
        // eslint-disable-next-line no-unused-vars
        let contactObj = null;
        let newContact = null;
        $.map(messenger.state.contacts,function (contact,k) {
            if(contactID === contact.contactID){
                newContact = contact;
                contactKey = k;
                newContact.lastMessage = message.message;
                newContact.dateAdded = message.dateAdded;
                newContact.dateAdded = message.dateAdded;
                newContact.senderID = message.sender_id;
                newContact.lastMessageSenderID = message.sender_id;
                messenger.state.contacts[k] = newContact;
            }
        });

        let newContactsList = messenger.state.contacts; // These kinds of stuff should be immutable
        if(contactKey !== null){
            newContactsList.splice(contactKey, 1);
            newContactsList.unshift(newContact);
            messenger.state.contacts = newContactsList;
        }

    },

    /**
     * Globally instantiates all href links within a conversation
     */
    initLinks: function(){
        $('.conversation-content .message-bubble').html(function(i, text) {
            var body = text.replace(
                // eslint-disable-next-line no-useless-escape
                /\bhttps:\/\/([\w\.-]+\.)+[a-z]{2,}\/.+\b/gi,
                '<a target="_blank" class="text-white" href="$&">$&</a>'
            );
            return body.replace(
                // eslint-disable-next-line no-useless-escape
                /\bhttp:\/\/([\w\.-]+\.)+[a-z]{2,}\/.+\b/gi,
                '<a target="_blank" class="text-white" href="$&">$&</a>'
            );
        });
    },

    /**
     * Globally instantiates all message attachments and groups them into individual galleries
     */
    initMessengerGalleries: function(){
        $('.message-box').each(function (index, item) {
            if($(item).find('.attachments-holder').children().length > 0){
                mswpScanPage($(item),'mswp');
            }
        });
    },

    /**
     * Replaces message's newlines with html break lines
     * @param text
     * @returns {*}
     */
    parseMessage: function(text){
        return filterXSS(text.replaceAll('\n','<br/>'));
    },

    /**
     * Loads UI elements for loaded messenger
     */
    hideEmptyChatElements: function () {
        $('.conversation-writeup').removeClass('hidden');
        $('.no-contacts').addClass('hidden');
    },

    /**
     * Instantiates & applies selectize on the new conversation modal
     */
    initSelectizeUserList: function(){
        $('#messageModal').on('show.bs.modal', function() {
            // TODO: Use default set to off, as on page load, there might be a second where the dialog is shown having the form displayed
            if(messenger.state.fetchedContactsListsCount === 1 && !messenger.state.hasAvailableFetchedContacts){
                $('.new-message-has-contacts').hide();
                $('.new-message-no-contacts').show();
            }
        });
        if(typeof Selectize !== 'undefined') {
            $('#select-repo').selectize({
                valueField: 'id',
                labelField: [],
                searchField: 'label',
                preload: true,
                options: [],
                create: false,
                render: {
                    option: function (item, escape) {
                        return '<div>' +
                            '<img class="searchAvatar mx-2 my-1" src="' + escape(item.avatar) + '" alt="">' +
                            '<span class="name">' + escape(item.name) + '</span>' +
                            '</div>';
                    },
                    item: function (item, escape) {
                        return '<div>' +
                            '<img class="searchAvatar mx-2" src="' + escape(item.avatar) + '" alt="">' +
                            '<span class="name">' + escape(item.name) + '</span>' +
                            '</div>';
                    }
                },
                load: function (query, callback) {
                    // if (!query.length) return callback();
                    $.ajax({
                        url:  app.baseUrl + '/my/messenger/getUserSearch',
                        type: 'POST',
                        data: {q: encodeURIComponent(query)},
                        dataType: 'json',
                        error: function () {
                            callback();
                        },
                        success: function (res) {
                            messenger.state.fetchedContactsListsCount += 1;
                            messenger.state.hasAvailableFetchedContacts = Object.values(res).length > 0 ? true : false;
                            callback(Object.values(res));
                        }
                    });
                }
            });
        }
    },

    /**
     * Shows up new conversation modal in UI
     */
    showNewMessageDialog: function () {
        $('#messageModal').modal('show');
    },

    /**
     * Instantiates the emoji picker messenger
     * @param post_id
     */
    initEmojiPicker: function(){
        try{
            const button = document.querySelector('.conversation-writeup .trigger');
            const picker = new EmojiButton(
                {
                    position: 'top-end',
                    theme: app.theme,
                    autoHide: false,
                    rows: 4,
                    recentsCount: 16,
                    emojiSize: '1.3em',
                    showSearch: false,
                }
            );
            picker.on('emoji', emoji => {
                document.querySelector('input').value += emoji;
                $('.messageBoxInput').val($('.messageBoxInput').val() + emoji);

            });
            button.addEventListener('click', () => {
                picker.togglePicker(button);
            });
        }
        catch (e) {
            // Maybe avoid ending up in here entirely
            // console.error(e)
        }

    },

    showSetPriceDialog: function () {
        $('#message-set-price-dialog').modal('show');
    },

    clearMessagePrice: function(){
        messenger.state.messagePrice = 5;
        messenger.state.isPaidMessage = false;
        $('#message-price').val(5);
        $('.message-price-lock').removeClass('d-none');
        $('.message-price-close').addClass('d-none');
        $('#message-set-price-dialog').modal('hide');
    },

    saveMessagePrice: function(){
        messenger.state.isPaidMessage = true;
        messenger.state.messagePrice = $('#message-price').val();
        if(parseInt(messenger.state.messagePrice) <= 0){
            $('#message-price').addClass('is-invalid');
            return false;
        }
        $('.message-price-lock').addClass('d-none');
        $('.message-price-close').removeClass('d-none');
        $('#message-set-price-dialog').modal('hide');
        $('#message-price').removeClass('is-invalid');
    },

    /**
     * Parses messenger's attachment previews
     * @param file
     * @returns {string}
     */
    parseMessageAttachment: function(file){
        let attachmentsHtml = '';
        switch (file.type) {
            case 'avi':
            case 'mp4':
            case 'wmw':
            case 'mpeg':
            case 'm4v':
            case 'moov':
            case 'mov':
                attachmentsHtml = `
                <a href="${file.path}" rel="mswp" title="" class="mr-2 mt-2">
                    <div class="video-wrapper">
                     <video class="video-preview" src="${file.path}" width="150" height="150" controls autoplay muted></video>
                    </div>
                 </a>`;
                break;
            case 'mp3':
            case 'wav':
            case 'ogg':
                attachmentsHtml = `
                <a href="${file.path}" rel="mswp" title="" class="mr-2 mt-2 d-flex align-items-center">
                    <div class="video-wrapper">
                         <audio id="video-preview" src="${file.path}" controls type="audio/mpeg" muted></audio>
                    </div>
                 </a>`;
                break;
            case 'png':
            case 'jpg':
            case 'jpeg':
                attachmentsHtml = `
                    <a href="${file.path}" rel="mswp" title="">
                        <img src="${file.thumbnail}" class="mr-2 mt-2">
                    </a>`;
                break;
            default:
                attachmentsHtml = `<img src="${file.thumbnail}" class="mr-2 mt-2">`;
                break;
        }
        return attachmentsHtml;
    },

    /**
     * Removes own comments
     * @param messageID
     */
    deleteMessage: function (messageID) {
        if(confirm(trans("Are you sure you want to delete this comment?"))){
            $.ajax({
                type: 'DELETE',
                dataType: 'json',
                url: app.baseUrl + '/my/messenger/delete/' + messageID,
                success: function () {
                    let element = $('*[data-messageid="'+messageID+'"]');
                    element.remove();
                    launchToast('success',trans('Success'),trans('Message removed'));
                },
                error: function (result) {
                    launchToast('danger',trans('Error'),result.responseJSON.message);
                }
            });
        }
    }

};

/**
 * Messenger contact component
 * @param contact
 * @returns {string}
 */
function contactElement(contact){
    const avatar = contact.receiverID === user.user_id ? contact.senderAvatar : contact.receiverAvatar;
    const name = contact.receiverID === user.user_id ? contact.senderName : contact.receiverName;
    return `
      <div class="col-12 d-flex pt-2 pb-2 contact-box contact-${contact.contactID}" onclick="messenger.fetchConversation(${contact.contactID})">
        <img src="${ avatar }" class="contact-avatar rounded-circle"/>
        <div class="m-0 ml-md-3 d-none d-lg-flex d-md-flex d-xl-flex justify-content-center flex-column text-truncate">
            <div class="m-0 text-truncate overflow-hidden contact-name ${contact.lastMessageSenderID !== user.user_id && contact.isSeen === 0 ? 'font-weight-bold' : ''}">${filterXSS(name)}</div>
            <small class="message-excerpt-holder d-flex text-truncate">
                <span class="text-muted mr-1 ${contact.lastMessageSenderID !== user.user_id ? 'd-none' : ''}"> ${trans('You')}: </span>
                <div class="m-0 text-muted contact-message text-truncate ${contact.lastMessageSenderID !== user.user_id && contact.isSeen === 0 ? 'font-weight-bold' : ''}" >${filterXSS(contact.lastMessage)}</div>
                <div class="d-flex"> <div class="font-weight-bold ml-1">${(contact.created_at !== null ? '∙' :'')}</div>${(contact.created_at !== null ? '&nbsp;' + contact.created_at : '')}</div>
            </small>
        </div>
      </div>
    `;
}

/**
 * Messenger message component
 * @param message
 * @returns {string}
 */
function messageElement(message){
    let isSender = false;
    if(parseInt(message.sender_id) === parseInt(user.user_id)){
        isSender = true;
    }

    let attachmentsHtml = '';
    message.attachments.map(function (file) {
        attachmentsHtml += messenger.parseMessageAttachment(file);
    });

    /* Paid message preview */
    if(message.hasUserUnlockedMessage === false && message.price > 0 && !isSender){
        return `
          <div class="col-12 no-gutters pt-1 pb-1 message-box px-0" data-messageid="${message.id}" id="m-${message.id}">
                    <div class="m-0 paid-message-box message-box text-break alert ${isSender ? 'alert-primary text-white' : 'alert-default'}">
                        <div class="col-12 d-flex mb-2 ${isSender ? 'sender d-flex flex-row-reverse pr-1' : 'pl-0'}">
                            ${message.message === null ? '' : messenger.parseMessage(message.message)}
                        </div>
                        <div class="d-flex justify-content-center">
                        ${lockedMessagePreview({'id' : message.id, 'price': message.price},message.sender)}
                        </div>
                    </div>
                </div>
          </div>
        `;
    }
    else{
        /* Regular message preview */
        return `
          <div class="col-12 no-gutters pt-1 pb-1 message-box px-0" data-messageid="${message.id}" id="m-${message.id}">
            ${message.message === null ? '' : messageBubble(isSender, message)}
            ${messageAttachments(isSender, attachmentsHtml, message)}
          </div>
    `;
    }

}

/**
 * Message bubble component
 * @param isSender
 * @param message
 * @returns {string}
 */
function messageBubble(isSender, message) {
    return `
        <div class="d-flex flex-row">
                <div class="col-12 d-flex  ${isSender ? 'sender d-flex flex-row-reverse pr-1' : 'pl-0'}">
                    <div class="m-0 message-bubble text-break alert ${isSender ? 'alert-primary text-white' : 'alert-default'}">${messenger.parseMessage(message.message)}</div>
                    ${isSender ? messageActions(true, message) : ''}
                </div>
        </div>
    `;
}

function messageAttachments(isSender, attachmentsHtml, message){
    return `
             <div class="col-12 d-flex  ${isSender ? 'sender d-flex flex-row-reverse pr-1' : 'pl-0'}">
                <div class="attachments-holder row no-gutters flex-row-reverse">
                    ${attachmentsHtml}
                </div>
                ${attachmentsHtml.length && isSender ? messageActions(true, message) : ''}
            </div>
     `;
}

function messageActions(showDeleteButton, message){
    return `
        <div class="d-flex message-actions-wrapper">
            ${showDeleteButton ? `
                <div class="d-flex justify-content-center align-items-center pointer-cursor mr-2">
                    <div class="to-tooltip message-action-button d-flex justify-content-center align-items-center"  data-placement="top" title="${trans('Delete')}" onClick="messenger.deleteMessage(${message.id})">
                        <ion-icon name="trash-outline"></ion-icon>
                    </div>
                </div>
            ` : ``}

           ${message.price > 0 ? `
            <div class="d-flex justify-content-center align-items-center mr-2">
                <div class="to-tooltip message-action-button d-flex justify-content-center align-items-center"  data-placement="top" title="${trans('Paid message')}">
                    <ion-icon name="cash-outline"></ion-icon>
                 </div>
            </div>
        ` : ``}
      </div>
    `;
}

/**
 * Locked message preview element
 * @param messageData
 * @param senderData
 * @returns {string}
 */
function lockedMessagePreview(messageData, senderData) {
    return `
            <div class="card ${app.theme === 'light' ? 'bg-gradient-faded-light-vertical' : 'bg-gradient-faded-dark-vertical'}">
              <div>
              <div class="lockedPreviewWrapper">
                  <img class="card-img" src="${messengerVars.lockedMessageSVGPath}" >
              </div>
                  <div class="card-img-overlay d-flex flex-column-reverse">
                           ${lockedMessagePaymentButton(messageData, senderData)}
                    </div>
                  </div>
              </div>
            </div>
`;
}

/**
 * Locked message payment button
 * @param messageData
 * @param senderData
 * @returns {string}
 */
function lockedMessagePaymentButton(messageData, senderData) {
    let modalData = `
                        data-toggle="modal"
                        data-target="#checkout-center"
                        data-type="message-unlock"
                        data-recipient-id="${senderData.id}"
                        data-amount="${messageData.price}"
                        data-first-name="${user.billingData.first_name}"
                        data-last-name="${user.billingData.last_name}"
                        data-billing-address="${user.billingData.billing_address}"
                        data-country="${user.billingData.country}"
                        data-city="${user.billingData.city}"
                        data-state="${user.billingData.state}"
                        data-postcode="${user.billingData.postcode}"
                        data-available-credit="${user.billingData.credit}"
                        data-username="${senderData.username}"
                        data-name="${senderData.first_name}"
                        data-avatar="${senderData.avatar}"
                        data-message-id="${messageData.id}"
    `;

    if(senderData.canEarnMoney === false) {
        modalData = `
            data-placement="top"
            title="${trans('This creator cannot earn money yet')}"
        `;
    }

    return `
                <button class="btn btn-round btn-primary btn-block d-flex align-items-center justify-content-center justify-content-lg-between mt-2 mb-0 to-tooltip" ${modalData}>
                <span class="d-none d-md-block">${trans('Locked message')}</span>  <span>${trans('Unlock for')} ${app.currencySymbol}${messageData.price}</span>
                </button>
    `;
}
