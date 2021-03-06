// pages/customer/center/center.js
//var custShowToast = require('../../../utils/custshowtoast');//引入消息提醒暴露
var common = require("../../utils/util.js");
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    userinfo:{},
    login:false
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function () {
     var that = this;
     wx.hideLoading()
  },
  //用户授权
  bindGetUserInfo: function (e) {
      wx.showLoading()
      var that = this;
      wx.login({
          success: function (res) 
          {
              if ( res.code ) 
              {
                  //发起网络请求,注册用户
                  wx.setStorageSync('code', res.code)
                  wx.request({
                      url: 'https://www.swahouse.com/api/Users/getWeixin',
                      data: 
                      {                            
                        code: res.code
                      },
                      header: 
                      {
                        'content-type': 'application/json'
                      },
                      success: function (res) {
                          //授权获取openid 和session_key
                          var openid      = res.data.data.openid
                          var session_key = res.data.data.session_key                         
                          //用户信息注册                         
                          wx.getUserInfo({
                                openIdList  : ['selfOpenId'],
                                lang        : 'zh_CN',
                                success     : (rsf) => {
                                    //获取用户信息                                           
                                    console.log(openid)      
                                    wx.request({
                                        url: app.globalData.globalUrl + "Users/usersRegister",
                                        data: {
                                          openId    : openid,
                                          city      : rsf.userInfo.city,
                                          avatarUrl : rsf.userInfo.avatarUrl,
                                          sex       : rsf.userInfo.gender,
                                          gender    : rsf.userInfo.city,
                                          province  : rsf.userInfo.province,
                                          country   : rsf.userInfo.country,
                                          nickName  : rsf.userInfo.nickName
                                        },
                                        method: "POST",
                                        header: {
                                          'content-type': 'application/x-www-form-urlencoded'
                                        },
                                        success: function (ssd) 
                                        {
                                              //保存用户信息 
                                              wx.setStorageSync('userinfo', ssd.data.data)
                                              //保存openid
                                              wx.setStorageSync('openid', openid)
                                              //保存session_key
                                              wx.setStorageSync('session_key', session_key)
                                              //设置数据信息
                                              that.setData({ 
                                                  userinfo : wx.getStorageSync('userinfo'),
                                                  login    : openid ? true : false
                                              })
                                              //获取优惠券
                                              that.getCoupons()
                                              wx.hideLoading()
                                        }                                       
                                    })
                                },
                                fail : function(e)
                                {
                                    wx.hideLoading()
                                }
                          })                                           
                      }
                      
                  })                 
              }else{
                  wx.showModal({ title : '异常提醒', 'content' : '授权异常，请重试~' })
              }
          },
          fail : function()
          {
            console.log('b')
          }
      });    
  },
  //是否登录
  isHaveOpenid : function(){    
    var openid = wx.getStorageSync('openid');  
    this.setData({
      login : openid ? true : false,
    })
    return this.data.login
  },
  //获取优惠券
  getCoupons : function(){
    var that = this
    var openid = wx.getStorageSync('openid') 
    if( openid ){
      wx.request({
        url: that.data.appurl +'Users/getUsersCoupon', //仅为示例，并非真实的接口地址
        data: {
          openid: openid
        },

        header: {
          'content-type': 'application/json'
        },
        success: function (res) {
         
          that.setData({
            coupons: res.data.data
          });

        }
      })
    }else{
        wx.showModal({ title : '异常提醒', 'content' : '登录信息过期，正在重新登录' })
        that.getUinfo()
    }
  },
  //获取用户信息
  getUinfo : function (){
      var that = this
      var openid = wx.getStorageSync('openid')    
      if( openid )
      {
         wx.request({
              url: that.data.appurl +'Users/getUsersIntegral', 
              data: {
                openid: openid
              },
              success:function(rsf)
              {
                var uinfo = rsf.data.data
                //更新用户信息 
                wx.setStorageSync('userinfo', uinfo)
                if (uinfo) {
                  that.setData({
                    userinfo: uinfo
                  })
                } else {
                  that.setData({
                    loadingHidden: true
                  })
                }
              }
          })
      }else{
          wx.showModal({ title : '异常提醒', 'content' : '授权信息异常！系统自动尝试~' })
          that.bindGetUserInfo()
      }
         
  },
  //拨打电话
  callTel : function()
  {
    wx.makePhoneCall({
      phoneNumber: '400-821-7980'
    })
  },
  //积分兑换跳转
  getLogs : function()
  {
      wx.navigateTo({
        url: '../logs/logs',
      })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    //如果用户已经登录
    if( this.isHaveOpenid() )
    {
        //获取用户信息
        this.getUinfo()
        //获取用户优惠券
        this.getCoupons()
    } 
    
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },


  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },
 
  /**
  * 跳转到详情页
  */
  toSetting: function (e) {
    var that = this;
    var uid = wx.getStorageSync('uid');
    var mobile = e.currentTarget.dataset.mobile;
    if (uid) {
      wx.navigateTo({
        url: '../setting/setting?mobile=' + mobile,
      })
      wx.request({
        url: that.data.appurl + "/user/user/loan",
        method: "GET",
        header: {
          'uid': wx.getStorageSync("uid"),
          'token': wx.getStorageSync("token")
        },
        success: function (res) {
          var obj = res.data.data;
          if (res.data.code == 10001) {
          //  wx.clearStorage();//清除本地存储
            //用户信息过期，重新登录
            custShowToast.showToast({ title: "账号信息过期，请重新登录" });
            setTimeout(function () {
              wx.redirectTo({
                url: '../../login/login'
              })
            }, 2000) //延迟时间
          }
        }
      })
    } else {
      wx.navigateTo({
        url: '../../login/login?from=center',
      })
    }
  },

  toSc:function(e){
    wx.navigateTo({
      url: '../score/score?from=center',
    })
  },
  tohisProject: function (e) {
    wx.navigateTo({
      url: '../activity/activity',
    })
  },

  toCurproject: function (e) {
    wx.navigateTo({
      url: '../collect/collect',
    })
  },
  /**
   * 跳转到客服页
   */
  toCall: function () {
    wx.navigateTo({
      url: '../address/address?from_=center',
    })
  },

  toRegisterTel: function (e) {
    wx.navigateTo({
      url: '../register/register',
    })
  },
 
})