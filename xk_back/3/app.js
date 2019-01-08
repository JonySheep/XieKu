//app.js
App({
  //框架初始化动作
  onLaunch: function() {
    
  },
  //用户授权注册
  register: function () {
    var that = this;
    wx.login({
      success: function (res) {
        if (res.code) 
        {
          console.log(res.code+'code....');
           //发起网络请求,注册用户
            wx.setStorageSync('code', res.code)
            wx.request({
                url: 'https://www.swahouse.com/api/Users/getWeixin',
                data: {                            
                  code: res.code
                },
                header: {
                  'content-type': 'application/json'
                },
                success: function (res) {
                    //授权获取openid 和session_key
                    wx.setStorageSync('openid', res.data.data.openid)
                    wx.setStorageSync('session_key', res.data.data.session_key)                                                   
                }
            })                 
        }
      }
    });
  },
  //获取相关培新信息
  getAbout: function () {
    var that = this
    wx.request({
      url: that.globalData.wxUrl + 'setting/get_set',
      data: {

      },
      success: function (res) {
        wx.setStorage({
          key: 'setting',
          data: res.data.data,
        })
      }
    })

  },
  //设置基础接口信息
  globalData: {
    userInfo: null,
    globalUrl: 'https://www.swahouse.com/api/',
    globalStaticUrl: 'https://www.swahouse.com/',
    wxUrl: 'https://www.swahouse.com/api/',
    mapKey:'6ZCBZ-WWY3X-J5A46-TNA75-7F5WQ-F7BCG'
  }

})