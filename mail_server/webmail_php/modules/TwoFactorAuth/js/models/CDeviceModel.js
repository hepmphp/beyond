'use strict'

const moment = require('moment')

const TextUtils = require('%PathToCoreWebclientModule%/js/utils/Text.js'),
  Types = require('%PathToCoreWebclientModule%/js/utils/Types.js'),
  App = require('%PathToCoreWebclientModule%/js/App.js')

const Settings = require('modules/%ModuleName%/js/Settings.js')

/**
 * @constructor
 * @param {object} oData
 * @returns {CDeviceModel}
 */
function CDeviceModel(oData) {
  this.sDeviceId = ''
  this.bCurrentDevice = false
  this.sDeviceName = ''
  this.sDeviceCustomName = ''
  this.bAuthenticated = false
  this.sDeviceExpiresDate = ''
  this.sDeviceLastUsageDate = ''

  if (oData) {
    this.parse(oData)
  }
}

/**
 * @param {Object} oData
 */
CDeviceModel.prototype.parse = function (oData) {
  var oExpMoment = moment.unix(oData.TrustTillDateTime),
    oUsageMoment = moment.unix(oData.LastUsageDateTime)
  this.sDeviceId = Types.pString(oData.DeviceId)
  this.bCurrentDevice = this.sDeviceId === App.getCurrentDeviceId()
  this.bAuthenticated = Types.pBool(oData.Authenticated)
  this.sDeviceName = Types.pString(oData.DeviceName)
  this.sDeviceCustomName = Types.pString(oData.DeviceCustomName)

  if (Settings.AllowTrustedDevices && oExpMoment.diff(moment()) > 0) {
    this.sDeviceExpiresDate = TextUtils.i18n('%MODULENAME%/LABEL_DEVICE_TRUST_TILL_DATE', {
      EXPDATE: oExpMoment.format('MMM D, YYYY'),
    })
  }
  this.sDeviceLastUsageDate = TextUtils.i18n('%MODULENAME%/LABEL_DEVICE_LAST_USAGE_DATE', {
    USAGEDATE: oUsageMoment.fromNow(),
  })
}

module.exports = CDeviceModel
