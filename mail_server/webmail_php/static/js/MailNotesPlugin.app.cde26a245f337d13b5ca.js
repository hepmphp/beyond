"use strict";
(self["webpackChunkafterlogic_aurora_platform"] = self["webpackChunkafterlogic_aurora_platform"] || []).push([[8],{

/***/ "1iYD":
/*!***********************************************!*\
  !*** ./modules/MailNotesPlugin/js/manager.js ***!
  \***********************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "M4cL");


module.exports = function (oAppData) {
	var
		_ = __webpack_require__(/*! underscore */ "C3HO"),
		ko = __webpack_require__(/*! knockout */ "p09A"),
		
		TextUtils = __webpack_require__(/*! modules/CoreWebclient/js/utils/Text.js */ "H20a"),
				
		App = __webpack_require__(/*! modules/CoreWebclient/js/App.js */ "9kOp"),

		sNotesName = 'Notes',
		sNotesFullName = sNotesName
	;
	
	function SetNotesFolder(koFolderList)
	{
		var
			sNameSpace = koFolderList().sNamespaceFolder,
			sDelimiter = koFolderList().sDelimiter
		;
		if (sNameSpace !== '')
		{
			sNotesFullName = sNameSpace + sDelimiter + sNotesName;
		}
		else
		{
			sNotesFullName = sNotesName;
		}
		var oNotesFolder = koFolderList().getFolderByFullName(sNotesFullName);
		if (oNotesFolder)
		{
			oNotesFolder.displayName = ko.observable(TextUtils.i18n('MAILNOTESPLUGIN/LABEL_FOLDER_NOTES'));
			oNotesFolder.usedAs = ko.observable(TextUtils.i18n('MAILNOTESPLUGIN/LABEL_USED_AS_NOTES'));
		}
	}
	
	if (App.isUserNormalOrTenant())
	{
		return {
			start: function (oModulesManager) {
				$('html').addClass('MailNotesPlugin');
				App.subscribeEvent('MailWebclient::ConstructView::before', function (oParams) {
					if (oParams.Name === 'CMailView')
					{
						var
							koFolderList = oParams.MailCache.folderList,
							koCurrentFolder = ko.computed(function () {
								return oParams.MailCache.folderList().currentFolder();
							}),
							CMessagePaneView = __webpack_require__(/*! modules/MailNotesPlugin/js/views/CMessagePaneView.js */ "n/wO"),
							oMessagePane = new CMessagePaneView(oParams.MailCache, _.bind(oParams.View.routeMessageView, oParams.View))
						;
						SetNotesFolder(koFolderList);
						koFolderList.subscribe(function () {
							SetNotesFolder(koFolderList);
						});
						koCurrentFolder.subscribe(function () {
							var sFullName = koCurrentFolder() ? koCurrentFolder().fullName() : '';
							if (sFullName === sNotesFullName)
							{
								oParams.View.setCustomPreviewPane('MailNotesPlugin', oMessagePane);
								oParams.View.setCustomBigButton('MailNotesPlugin', function () {
									oModulesManager.run('MailWebclient', 'setCustomRouting', [sFullName, 1, '', '', '', 'create-note']);
								}, TextUtils.i18n('MAILNOTESPLUGIN/ACTION_NEW_NOTE'));
								oParams.View.resetDisabledTools('MailNotesPlugin', ['spam', 'move', 'mark']);
							}
							else
							{
								oParams.View.removeCustomPreviewPane('MailNotesPlugin');
								oParams.View.removeCustomBigButton('MailNotesPlugin');
								oParams.View.resetDisabledTools('MailNotesPlugin', []);
							}
						});
					}
				});
				App.subscribeEvent('MailWebclient::ConstructView::after', function (oParams) {
					if (oParams.Name === 'CMessageListView' && oParams.MailCache)
					{
						var
							koCurrentFolder = ko.computed(function () {
								return oParams.MailCache.folderList().currentFolder();
							})
						;
						koCurrentFolder.subscribe(function () {
							var sFullName = koCurrentFolder() ? koCurrentFolder().fullName() : '';
							if (sFullName === sNotesFullName)
							{
								oParams.View.customMessageItemViewTemplate('MailNotesPlugin_MessageItemView');
							}
							else
							{
								oParams.View.customMessageItemViewTemplate('');
							}
						});
					}
				});
				App.subscribeEvent('MailWebclient::MessageDblClick::before', _.bind(function (oParams) {
					if (oParams.Message && oParams.Message.folder() === sNotesFullName)
					{
						oParams.Cancel = true;
					}
				}, this));
			}
		};
	}
	
	return null;
};


/***/ }),

/***/ "n/wO":
/*!**************************************************************!*\
  !*** ./modules/MailNotesPlugin/js/views/CMessagePaneView.js ***!
  \**************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var
	_ = __webpack_require__(/*! underscore */ "C3HO"),
	$ = __webpack_require__(/*! jquery */ "M4cL"),
	ko = __webpack_require__(/*! knockout */ "p09A"),

	TextUtils = __webpack_require__(/*! modules/CoreWebclient/js/utils/Text.js */ "H20a"),

	Ajax = __webpack_require__(/*! modules/CoreWebclient/js/Ajax.js */ "EFhx"),
	Api = __webpack_require__(/*! modules/CoreWebclient/js/Api.js */ "/QeJ"),
	ModulesManager = __webpack_require__(/*! modules/CoreWebclient/js/ModulesManager.js */ "TdEd"),
	MailCache = null
;

/**
 * @constructor
 * @param {object} oMailCache
 * @param {function} fRouteMessageView
 */
function CMessagePaneView(oMailCache, fRouteMessageView)
{
	MailCache = oMailCache;
	this.fRouteMessageView = fRouteMessageView;
	this.currentMessage = MailCache.currentMessage;
	this.messageText = ko.observable('');
	this.messageText.focused = ko.observable(false);
	ko.computed(function () {
		this.messageText();
		this.messageText.focused(true);
	}, this).extend({ throttle: 5 }); ;
	this.sMessageUid = '';
	this.sMessageText = '';
	this.isLoading = ko.observable(false);
	this.isSaving = ko.observable(false);
	this.createMode = ko.observable(false);
	this.saveButtonText = ko.computed(function () {
		return this.isSaving() ? TextUtils.i18n('COREWEBCLIENT/ACTION_SAVE_IN_PROGRESS') : TextUtils.i18n('COREWEBCLIENT/ACTION_SAVE');
	}, this);

	this.bBinded = false;
}

CMessagePaneView.prototype.ViewTemplate = 'MailNotesPlugin_MessagePaneView';
CMessagePaneView.prototype.ViewConstructorName = 'CMessagePaneView';

CMessagePaneView.prototype.onShow = function ()
{
	this.currentMessageSubscription = this.currentMessage.subscribe(this.onCurrentMessageSubscribe, this);
	this.bShown = true;
};

CMessagePaneView.prototype.onHide = function ()
{
	this.currentMessageSubscription?.dispose();
	this.bShown = false;
};

/**
 * Checks if there are changes in message pane.
 * @returns {Boolean}
 */
CMessagePaneView.prototype.hasUnsavedChanges = function ()
{
	var oMessage = this.currentMessage();
	return (!oMessage || this.sMessageUid === oMessage.uid()) && this.sMessageText !== this.messageText();
};

/**
 * Discards changes in message pane.
 */
CMessagePaneView.prototype.discardChanges = function ()
{
	if (!this.currentMessage())
	{
		this.sMessageUid = '';
		this.sMessageText = '';
		this.messageText('');
	}
};

CMessagePaneView.prototype.getSubjectFromText = function (sText)
{
	var
		aText = sText.split(/\r\n|\n/i),
		sSubject = _.find(aText, function (sTextPart) {
			return $.trim(sTextPart) !== '';
		})
	;

	sSubject = $.trim(sSubject);
	if (sSubject.length > 50)
	{
		sSubject = sSubject.substring(0, 50);
	}

	return sSubject;
};

CMessagePaneView.prototype.onCurrentMessageSubscribe = function ()
{
	var oMessage = this.currentMessage();

	if (oMessage)
	{
		if (oMessage.isPlain())
		{
			this.messageText(oMessage.text());
		}
		else
		{
			this.messageText(TextUtils.htmlToPlain(oMessage.text()));
		}
		this.sMessageUid = oMessage.uid();
		this.sMessageText = this.messageText();
		this.isLoading(oMessage.uid() !== '' && !oMessage.completelyFilled());
		if (!oMessage.completelyFilled())
		{
			var sbscr = oMessage.completelyFilled.subscribe(function () {
				this.onCurrentMessageSubscribe();
				sbscr.dispose();
			}, this);
		}
		this.isSaving(false);
	}
	else
	{
		this.sMessageUid = '';
		this.sMessageText = '';
		this.messageText('');
	}
};

/**
 * @param {Object} $MailViewDom
 */
CMessagePaneView.prototype.onBind = function ($MailViewDom)
{
	if (!this.bBinded)
	{
		ModulesManager.run('SessionTimeoutWeblient', 'registerFunction', [_.bind(function () {
			this.saveNote();
		}, this)]);

		$(document).on('keydown', $.proxy(function(ev) {
			if (ev.ctrlKey && ev.keyCode === Enums.Key.s)
			{
				ev.preventDefault();
				this.saveNote();
			}
		}, this));

		this.bBinded = true;
	}
};

CMessagePaneView.prototype.onRoute = function (aParams, oParams)
{
	var oIdentifiers = MailCache.getMessageActualIdentifiers(MailCache.currentAccountId(), oParams.Folder, oParams.Uid);

	MailCache.setCurrentMessage(oIdentifiers.iAccountId, oIdentifiers.sFolder, oIdentifiers.sUid);
	if (oParams.Custom === 'create-note')
	{
		this.messageText('');
		this.createMode(true);
	}
	else
	{
		this.createMode(false);
	}
	this.isSaving(false);
};

CMessagePaneView.prototype.saveNote = function ()
{
	if (this.createMode())
	{
		this.saveNewNote();
	}
	else
	{
		this.saveEditedNote();
	}
};

CMessagePaneView.prototype.saveNewNote = function ()
{
	var
		oFolder = MailCache.getCurrentFolder(),
		oParameters = {
			'AccountID': MailCache.currentAccountId(),
			'FolderFullName': oFolder.fullName(),
			'Text': TextUtils.encodeHtml(this.messageText()).replace(/\n/g, '<br />').replace(/\r\n/g, '<br />'),
			'Subject': this.getSubjectFromText(this.messageText())
		}
	;
	this.isSaving(true);
	this.sMessageText = this.messageText();
	Ajax.send('MailNotesPlugin', 'SaveNote', oParameters, function (oResponse) {
		this.isSaving(false);
		if (oResponse.Result)
		{
			if (this.bShown)
			{
				var sbscr = MailCache.messagesLoading.subscribe(function () {
					if (this.bShown && !MailCache.messagesLoading() && !this.currentMessage())
					{
						this.fRouteMessageView(oParameters.FolderFullName, oResponse.Result);
						sbscr.dispose();
					}
				}, this);
			}
		}
		else
		{
			Api.showErrorByCode(oResponse, TextUtils.i18n('MAILNOTESPLUGIN/ERROR_NOTE_SAVING'));
		}
		MailCache.executeCheckMail(true);
	}, this);
};

CMessagePaneView.prototype.saveEditedNote = function (oMessage)
{
	if (!oMessage)
	{
		oMessage = this.currentMessage();
	}
	if (oMessage)
	{
		var
			oParameters = {
				'AccountID': MailCache.currentAccountId(),
				'FolderFullName': oMessage.folder(),
				'MessageUid': oMessage.uid(),
				'Text': TextUtils.encodeHtml(this.messageText()).replace(/\n/g, '<br />').replace(/\r\n/g, '<br />'),
				'Subject': this.getSubjectFromText(this.messageText())
			},
			oFolder = MailCache.getFolderByFullName(MailCache.currentAccountId(), oMessage.folder())
		;
		oFolder.markDeletedByUids([oMessage.uid()]);
		MailCache.excludeDeletedMessages();
		this.isSaving(true);
		this.sMessageText = this.messageText();
		Ajax.send('MailNotesPlugin', 'SaveNote', oParameters, function (oResponse) {
			this.isSaving(false);
			if (oResponse.Result)
			{
				if (this.bShown)
				{
					var sbscr = MailCache.messagesLoading.subscribe(function () {
						if (this.bShown && !MailCache.messagesLoading() && !this.currentMessage())
						{
							this.fRouteMessageView(oParameters.FolderFullName, oResponse.Result);
							sbscr.dispose();
						}
					}, this);
				}
			}
			else
			{
				Api.showErrorByCode(oResponse, TextUtils.i18n('MAILNOTESPLUGIN/ERROR_NOTE_SAVING'));
			}
			MailCache.executeCheckMail(true);
		}, this);
	}
};

CMessagePaneView.prototype.cancel = function ()
{
	this.sMessageText = this.messageText();
	ModulesManager.run('MailWebclient', 'setCustomRouting', ['Notes', 1, '', '', '', '']);
};

module.exports = CMessagePaneView;


/***/ })

}]);