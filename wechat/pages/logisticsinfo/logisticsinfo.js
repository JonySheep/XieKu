// pages/xiadan/xiadan.js
var app = getApp();
var common = require("../../utils/util.js");
import templet from "../common/lib/templet.js";

Page({

  /**
   * 页面的初始数据
   */
  data: {
    winHeight: 0,
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    speedlist: [],
    company: '',
    send_time: '',
    order_sn: ''

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this;
    wx.request({
      url: getApp().globalData.globalUrl + 'order/getLogistics',
      data: {
        order_number: options.num,
        order_type: options.order_type
      },
      header: {
        'content-type': 'application/json'
      },
      success: function(data) {
        if (data.data.code == 200) {
          that.setData({
            company: data.data.data.company,
            send_time: data.data.data.send_time,
            order_sn: data.data.data.order_sn,
            speedlist: data.data.data.list,
          });
        } else {
          wx.showToast({
            title: data.data.data.err,
            icon: 'none'
          })
        }

      }
    })



  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function() {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function() {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})