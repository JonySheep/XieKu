// pages/category/category.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    currentTab:0,
    obj1:{
      img1:"https://www.swahouse.com/index.php/image/welfare7.png",
      title1:"ONG 冷凝系列***",
      score1:666,
      img2: "https://www.swahouse.com/index.php/image/welfare7.png",
      title2: "ONG 冷凝系列***",
      score2: 666,

    },
    category:{}
  },
  swichNav:function(event){
    console.log(event.currentTarget);
    this.setData({
      currentTab: event.currentTarget.dataset.current
    })
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
    wx.hideLoading()
    var self = this;

    app.globalData.isaddcar = true
    app.changecarNumFn()

    wx.request({
      url: self.data.appurl+'Brand/getBrand', //仅为示例，并非真实的接口地址
      //self.data.appurl + 'category'
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        self.setData({
          category: res.data.data
        });

      }
    })


    

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
  toCategoryDetail: function (event) {

    wx.navigateTo({
      url: '../categorydetails/categorydetails?id=' + event.currentTarget.dataset.categoryid,
    })
  },
})