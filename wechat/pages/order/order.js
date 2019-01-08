// pages/order/order.js
var common = require("../../utils/util.js");
import templet from "../common/lib/templet.js";

Page({

  /**
   * 页面的初始数据
   */
  data: {
    promptList:["待付款", "待发货", "待收货", "", "", "已完成"],
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    ceshi:[],
    // 页面配置    
    winWidth: 0,
    winHeight: 0,
    // tab切换   
    currentTab: 0,
    currentTabName: "",
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    // 获取系统信息   
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          winWidth: res.windowWidth,
          winHeight: res.windowHeight
        });
      }
    }); 
    that.setData({
      currentTab: wx.getStorageSync('active_id')
    });
    that.getorderList();
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
    wx.switchTab({
      url: '../center/center',
      fail: function (e) {
        console.log(e)
      }
    })
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

  bindChange: function (e) {
    var that = this;
    that.setData({ 
      currentTab: e.detail.current, 
      state: "已完成",
    });
  },  
  /** 
   * 点击tab切换 
   */
  swichNav: function (e) {
    var that = this;
    if (this.data.currentTab === e.target.dataset.current) {
      return false;
    } else {
      var promptList = ["待付款", "待发货", "待收获","","", "已完成"];
      that.setData({
        currentTab: e.target.dataset.current,
        currentTabName: that.data.promptList[e.target.dataset.current],
        state:"已完成",
      })
      that.getorderList()
    }
  },

  // 获取订单列表
  getorderList: function () {
    var that = this;
    common.httpG('order/orderList', {
      openid: wx.getStorageSync('openid'),
      status: that.data.currentTab
    }, function (data) {
      that.setData({ceshi:data.data})
    })
  },

  //点击付款
  gopayFn:function(e){
    templet.gopayFn(e)
  },

  //取消订单
  cansolorderFn: function (e) {
    var that = this;
    templet.cansolorderFn(e,function(){
      that.getorderList()
    })
    
  },

  // 查看物流
  checkorderinfoFn: function (e) {
    templet.checkorderinfoFn(e)
  },

  // 确认收货
  onSureOrderFn: function (e) {
    var that = this;
    templet.onSureOrderFn(e,function(){
      that.getorderList()
    }) 
  },

  // 申请售后
  onServerFn: function(){
    templet.onServerFn()
  }

})