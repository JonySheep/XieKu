var app = getApp();
const wxurl = app.globalData.wxUrl;


const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}
function getUserName() {
  var username = wx.getStorageSync('uinfo');
  if (!username) {
    app.register();
  }
  return username;
}
function httpG(url, data, callback) {
  wx.showLoading({
    title: '努力加载中^^...',
    mask:true,
  })
  wx.request({
    url: wxurl + url,
    data: data,
    success: function (res) {
      callback(res.data);
    },
    fail: function (res) {
      console.log('request-get error:', res);
    },
    complete: function (res) {
      wx.hideLoading();
      console.log("get-complete:", res.data)
      if (res.data.code && res.data.code != 0) {
        wx.showToast({
          title: res.data.err,
          mask: true,
        })
      }
    }
  })
}
function httpP(url, data, callback) {
  wx.request({
    url: wxurl + url,
    data: data,
    method: "post",
    header: {
      'content-type': 'application/x-www-form-urlencoded'
    },
    success: function (res) {
      if (res.data.code == 1) {
        callback(res.data);
      }
    },
    fail: function (res) {
      console.log('request-post error:', res);
    },
    complete: function (res) {
      console.log("post-complete:", res.data)
      if (res.data.code && res.data.code != 1) {
        wx.showToast({
          title: res.data.err,
          mask: true,
        })
      }
    }
  })
}

// 本地缓存读取
const getStorageData = (key, cb) => {
  let self = this;

  // 将数据存储在本地缓存中指定的 key 中，会覆盖掉原来该 key 对应的内容，这是一个异步接口
  wx.getStorage({
    key: key,
    success(res) {
      cb && cb(res.data);
    },
    fail(err) {
      let msg = err.errMsg || '';
      if (/getStorage:fail/.test(msg)) {
        setStorageData(key)
      }
    }
  })
}
// 本地缓存存储
const setStorageData = (key, value = '', cb) => {
  wx.setStorage({
    key: key,
    data: value,
    success() {
      cb && cb();
    }
  })
}

module.exports = {
  formatTime: formatTime,
  httpP: httpP,
  httpG: httpG,
  getUserName: getUserName,
  formatNumber: formatNumber
}
