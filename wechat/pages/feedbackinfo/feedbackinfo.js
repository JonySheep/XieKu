// pages/xiadan/xiadan.js
var app = getApp();
var common = require("../../utils/util.js");

Page({

  /**
   * 页面的初始数据
   */
  data: {
    winHeight: 0,
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    text:'',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
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

  // 输入提交意见反馈内容
  onChangeFn:function(options){
      var that = this;
      that.setData({
        text: options.detail.value
      });
  },

  // 提交意见反馈
  onSubmitFn(){
    var self = this;
    var velue = this.data.text;
    var openid = wx.getStorageSync('openid');
    if (velue){
      wx.request({
        url: self.data.appurl + 'users/feedBack',
        header: {
          'content-type': 'application/json'
        },
        data:{
          openid:openid,
          content: velue
        },
        success: function (res) {
          console.log(res);
           if(res.data.code == 200){
             self.setData({
               text: ''
             });
             wx.showToast({
               title: '提交成功',
             })
           }
        }
      });
    }
  }

})