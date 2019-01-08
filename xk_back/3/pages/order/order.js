// pages/order/order.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
   
    ceshi:[
       {
        img1: "https://www.swahouse.com/image/welfare7.png",
        title1: "ONE 冷凝系列 -CLAS-ONE 酷能",
        score1: 666,
        state: "兑换中",

      },
      {
        img1: "https://www.swahouse.com/image/welfare7.png",
        title1: "ONE 冷凝系列 -CLAS-ONE 酷能",
        score1: 666,
        state: "兑换中",

      },

    ],
    
    // 页面配置    
    winWidth: 0,
    winHeight: 0,
    // tab切换   
    currentTab: 0,
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
  bindChange: function (e) {
    var that = this;
    that.setData({ currentTab: e.detail.current, state: "已完成",});
  },  
  /** 
   * 点击tab切换 
   */
  swichNav: function (e) {
    var that = this;
    if (this.data.currentTab === e.target.dataset.current) {
      return false;
    } else {
      that.setData({
        currentTab: e.target.dataset.current,
        state:"已完成",
      })
    }
  },
  
})