"use strict";
(self["webpackChunkafterlogic_aurora_platform"] = self["webpackChunkafterlogic_aurora_platform"] || []).push([[4],{

/***/ "150G":
/*!***************************************!*\
  !*** ./modules/Google/js/Settings.js ***!
  \***************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var
	_ = __webpack_require__(/*! underscore */ "C3HO"),
	ko = __webpack_require__(/*! knockout */ "p09A"),
	Types = __webpack_require__(/*! modules/CoreWebclient/js/utils/Types.js */ "KC/L")
;

module.exports = {
	ServerModuleName: 'Google',
	HashModuleName: 'google',

	Connected: false,

	EnableModule: false,
	Id: '',
	Secret: '',
	Key: '',
	Scopes: [],

	/**
	 * Initializes settings from AppData object sections.
	 *
	 * @param {Object} oAppData Object contained modules settings.
	 */
	init: function (oAppData)
	{
		var oAppDataSection = _.extend({}, oAppData[this.ServerModuleName] || {}, oAppData['Google'] || {});

		if (!_.isEmpty(oAppDataSection))
		{
			this.Connected = Types.pBool(oAppDataSection.Connected, this.Connected);

			this.EnableModule = Types.pBool(oAppDataSection.EnableModule, this.EnableModule);
			this.Id = Types.pString(oAppDataSection.Id, this.Id);
			this.Secret = Types.pString(oAppDataSection.Secret, this.Secret);
			this.Key = Types.pString(oAppDataSection.Key, this.Key);
			this.Scopes = Types.pArray(oAppDataSection.Scopes, this.Scopes);
		}
	},

	/**
	 * Returns copy of Scopes with observable Value parameter.
	 *
	 * @returns {Array}
	 */
	getScopesCopy: function ()
	{
		var aScopesCopy = [];
		_.each(this.Scopes, function (oScope) {
			aScopesCopy.push({
				Description: oScope.Description,
				Name: oScope.Name,
				Value: ko.observable(oScope.Value)
			});
		});
		return aScopesCopy;
	},

	/**
	 * Updates Connected and Scopes parameters.
	 *
	 * @param {boolean} bConnected New value of Connected parameter.
	 * @param {array} aScopes New value of Scopes parameter.
	 */
	updateScopes: function (bConnected, aScopes)
	{
		var aNewScopes = [];
		_.each(aScopes, function (oScope) {
			aNewScopes.push({
				Description: oScope.Description,
				Name: oScope.Name,
				Value: oScope.Value()
			});
		});
		this.Connected = bConnected;
		this.Scopes = aNewScopes;
	},

	/**
	 * Updates settings that is edited by administrator.
	 *
	 * @param {boolean} bEnableModule New value of EnableModule parameter.
	 * @param {string} sId New value of Id parameter.
	 * @param {string} sSecret New value of Secret parameter.
	 * @param {string} sKey New value of Key parameter.
	 * @param {array} aScopes New value of Scopes parameter.
	 */
	updateAdmin: function (bEnableModule, sId, sSecret, sKey, aScopes)
	{
		this.EnableModule = bEnableModule;
		this.Id = sId;
		this.Secret = sSecret;
		this.Key = sKey;
		this.Scopes = aScopes;
	}
};


/***/ }),

/***/ "t6Yg":
/*!**************************************!*\
  !*** ./modules/Google/js/manager.js ***!
  \**************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



module.exports = function (oAppData) {
	var
		_ = __webpack_require__(/*! underscore */ "C3HO"),

		TextUtils = __webpack_require__(/*! modules/CoreWebclient/js/utils/Text.js */ "H20a"),

		App = __webpack_require__(/*! modules/CoreWebclient/js/App.js */ "9kOp"),

		Settings = __webpack_require__(/*! modules/Google/js/Settings.js */ "150G")
	;

	Settings.init(oAppData);

	if (App.isUserNormalOrTenant() && Settings.EnableModule && _.isArray(Settings.Scopes) && Settings.Scopes.length > 0)
	{
		return {
			start: function (ModulesManager) {
				ModulesManager.run('SettingsWebclient', 'registerSettingsTab', [
					function () { return __webpack_require__(/*! modules/Google/js/views/GoogleSettingsFormView.js */ "nAbd"); },
					Settings.HashModuleName,
					TextUtils.i18n('GOOGLE/LABEL_SETTINGS_TAB')
				]);
			}
		};
	}

	return null;
};


/***/ }),

/***/ "nAbd":
/*!***********************************************************!*\
  !*** ./modules/Google/js/views/GoogleSettingsFormView.js ***!
  \***********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {



var
	_ = __webpack_require__(/*! underscore */ "C3HO"),
	$ = __webpack_require__(/*! jquery */ "M4cL"),
	ko = __webpack_require__(/*! knockout */ "p09A"),

	Types = __webpack_require__(/*! modules/CoreWebclient/js/utils/Types.js */ "KC/L"),
	UrlUtils = __webpack_require__(/*! modules/CoreWebclient/js/utils/Url.js */ "Tt1R"),

	Ajax = __webpack_require__(/*! modules/CoreWebclient/js/Ajax.js */ "EFhx"),
	Api = __webpack_require__(/*! modules/CoreWebclient/js/Api.js */ "/QeJ"),
	App = __webpack_require__(/*! modules/CoreWebclient/js/App.js */ "9kOp"),
	ModulesManager = __webpack_require__(/*! modules/CoreWebclient/js/ModulesManager.js */ "TdEd"),
	WindowOpener = __webpack_require__(/*! modules/CoreWebclient/js/WindowOpener.js */ "mGms"),

	CAbstractSettingsFormView = ModulesManager.run('SettingsWebclient', 'getAbstractSettingsFormViewClass'),

	Settings = __webpack_require__(/*! modules/Google/js/Settings.js */ "150G")
;

/**
* @constructor
*/
function CGoogleSettingsFormView()
{
	CAbstractSettingsFormView.call(this, Settings.ServerModuleName);

	this.connected = ko.observable(Settings.Connected);
	this.scopes = ko.observable(Settings.getScopesCopy());
	this.bRunCallback = false;

	window.googleConnectCallback = _.bind(function (bResult, sErrorCode, sModule) {
		this.bRunCallback = true;

		if (!bResult)
		{
			Api.showErrorByCode({'ErrorCode': Types.pInt(sErrorCode), 'Module': sModule}, '', true);
		}
		else
		{
			this.connected(true);
			this.updateSavedState();
			Settings.updateScopes(this.connected(), this.scopes());
		}
	}, this);
}

_.extendOwn(CGoogleSettingsFormView.prototype, CAbstractSettingsFormView.prototype);

CGoogleSettingsFormView.prototype.ViewTemplate = 'Google_GoogleSettingsFormView';

/**
 * Returns current values of changeable parameters. These values are used to compare with their previous version.
 * @returns {Array}
 */
CGoogleSettingsFormView.prototype.getCurrentValues = function()
{
	var aScopesValues = _.map(this.scopes(), function (oScope) {
		return oScope.Name + oScope.Value();
	});
	return [
		this.connected(),
		aScopesValues
	];
};

/**
 * Reverts values of changeable parameters to default ones.
 */
CGoogleSettingsFormView.prototype.revertGlobalValues = function()
{
	this.connected(Settings.Connected);
	this.scopes(Settings.getScopesCopy());
};

/**
 * Checks if connect is allowed and tries to connect in that case.
 */
CGoogleSettingsFormView.prototype.checkAndConnect = function ()
{
	var
		oParams = {
			'Scopes': [],
			'Service': 'google',
			'AllowConnect': true
		},
		oAuthScope = _.find(this.scopes(), function (oScope) {
			return oScope.Name === 'auth';
		}),
		bAuthOn = !!oAuthScope && !!oAuthScope.Value(),
		oAuthGlobalScope = _.find(Settings.getScopesCopy(), function (oScope) {
			return oScope.Name === 'auth';
		}),
		bGlobalAuthOn = !!oAuthGlobalScope && !!oAuthGlobalScope.Value()
	;

	_.each(this.scopes(), function (oScope) {
		if (oScope.Value())
		{
			oParams.Scopes.push(oScope.Name);
		}
	});

	App.broadcastEvent('OAuthAccountChange::before', oParams);

	if (oParams.AllowConnect && (bAuthOn || bAuthOn === bGlobalAuthOn || !bAuthOn && App.isAccountDeletingAvailable()))
	{
		this.connect(oParams.Scopes);
	}
};

/**
 * Tries to connect user to google account.
 * @param {array} aScopes
 */
CGoogleSettingsFormView.prototype.connect = function (aScopes)
{
	$.removeCookie('oauth-scopes');
	$.cookie('oauth-scopes', aScopes.join('|'));
	$.cookie('oauth-redirect', 'connect');
	this.bRunCallback = false;
	var
		oWin = WindowOpener.open(UrlUtils.getAppPath() + '?oauth=google-connect', 'Google'),
		iIntervalId = setInterval(_.bind(function() {
			if (oWin.closed)
			{
				clearInterval(iIntervalId);
				if (!this.bRunCallback)
				{
					window.location.reload();
				}
				else
				{
					App.broadcastEvent('OAuthAccountChange::after');
					this.updateSavedState();
					Settings.updateScopes(this.connected(), this.scopes());
				}
			}
		}, this), 1000)
	;
};

/**
 * Checks if disconnect is allowed and disconnects in that case.
 */
CGoogleSettingsFormView.prototype.checkAndDisconnect = function ()
{
	var
		oParams = {
			'Service': 'google',
			'AllowDisconnect': true
		},
		oAuthGlobalScope = _.find(Settings.getScopesCopy(), function (oScope) {
			return oScope.Name === 'auth';
		}),
		bGlobalAuthOn = !!oAuthGlobalScope && !!oAuthGlobalScope.Value()
	;

	App.broadcastEvent('OAuthAccountChange::before', oParams);

	if (oParams.AllowDisconnect && (!bGlobalAuthOn || App.isAccountDeletingAvailable()))
	{
		this.disconnect();
	}
};

/**
 * Disconnects user from google account.
 */
CGoogleSettingsFormView.prototype.disconnect = function ()
{
	Ajax.send(Settings.ServerModuleName, 'DeleteAccount', null, function (oResponse) {
		if (oResponse.Result)
		{
			this.connected(false);
			_.each(this.scopes(), function (oScope) {
				oScope.Value(false);
			});
			App.broadcastEvent('OAuthAccountChange::after');
			this.updateSavedState();
			Settings.updateScopes(this.connected(), this.scopes());
		}
		else
		{
			Api.showErrorByCode(oResponse, '', true);
		}
	}, this);
};

module.exports = new CGoogleSettingsFormView();


/***/ })

}]);