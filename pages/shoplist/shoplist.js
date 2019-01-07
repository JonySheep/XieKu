// pages/shoplist/shoplist.js
var common = require("../../utils/util.js");
Page({

  /**
   * 页面的初始数据
   */
  data: {
    winHeight:0,
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    storeList: {},
    checkstoreid: '',
    checkstorename: ''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this;
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          winWidth: res.windowWidth,
          winHeight: res.windowHeight,
        });
      }
    });
  },

  // 选择门店
  chosestroe: function (e) {
    var id = e.currentTarget.dataset.id;
    var name = e.currentTarget.dataset.name;
    this.setData({
      checkstoreid: e.currentTarget.dataset.id,
      checkstoreid: e.currentTarget.dataset.name,
    })
    var pages = getCurrentPages();
    var currPage = pages[pages.length - 1];   //当前页面
    var prevPage = pages[pages.length - 2];  //上一个页面

    //直接调用上一个页面的setData()方法，把数据存到上一个页面中去
    prevPage.setData({
      checkstoreinfo: { 
        id: e.currentTarget.dataset.id, 
        name: e.currentTarget.dataset.name 
      }
    })
    
    wx.navigateBack()
    // wx.navigateTo({
    //   url: "../xiadan/xiadan?name=" + name + '&id=' + id,
    // });
  },

  //获取门店列表 
  getStore: function () {
    var self = this;
    wx.getLocation({
      type: 'wgs84',
      success: function (res) {
        var latitude = res.latitude
        var longitude = res.longitude
        var speed = res.speed
        var accuracy = res.accuracy;
        console.log(res);
        common.httpG('goods/storeList?latitude=' + latitude + '&longitude=' + longitude, {
        }, function (data) {
          if (data.data) {
            self.setData({
              storeList: data.data,
            });
          }
        })
      }
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
    this.getStore();
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

  // 获取店铺坐标打开地图
  openMapFn(e){
    
    var latitude = e.currentTarget.dataset.lat;
    var longitude = e.currentTarget.dataset.lng; 
    var name = e.currentTarget.dataset.name;
    var address = e.currentTarget.dataset.address;
    
    if (latitude && longitude){

      console.log(e)

      wx.openLocation({
        longitude: Number(longitude),
        latitude: Number(latitude),
        name: name,
        address: address,
        scale: 28
      })

    }else{
      wx.showModal({
        title: '提示',
        content: '未获取到店铺位置！',
      })
    }
  }
})