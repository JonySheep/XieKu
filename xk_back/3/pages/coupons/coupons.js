// pages/activity/activity.js
var common = require("../../utils/util.js");
var app = getApp(); 
Page({

  /**
   * 页面的初始数据
   */
  data: {
     
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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
    var self = this;
    var users_id = wx.getStorageSync('userinfo').id

    //优惠券展示
    wx.request({
      url: 'https://www.swahouse.com/api/coupon/getCouponList',
      data: {
          users_id : users_id ? users_id : 0
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        self.setData({
          coupons: res.data.data
        });

      }
    })

  },
  addCoupons:function(e){
    var couponid = e.target.dataset.couponid;
    console.log(couponid);
    var id = wx.getStorageSync('userinfo').id;
    if( id )
    {
      wx.request({
        url: app.globalData.globalUrl + "coupon/getCoupon",
        data: {
          users_id : id,
          coupon_id: couponid
        },
        method: "POST",
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
            //保存uid 
            console.log(res)
            if( res.data.code === 200 )
            {
              wx.showToast({
                title: res.data.data.rsf
              })
            }else{
              wx.showToast({
                title: res.data.err,
                icon: 'none',
                duration: 2000
              })              
            }        
        }
      })
    }else{
      wx.showToast({
        title: '请先去我的个人中心授权认证',
        icon: 'none',
        duration: 2000
      })     
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

  }
})