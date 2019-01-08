// pages/xiadan/xiadan.js
var app = getApp();
var common = require("../../utils/util.js");
var QQMapWX = require('../../utils/qqmap-wx-jssdk.js');
var qqmapsdk;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    winHeight: 0,
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    reminder:true,
    peisongHidden:true,
    name:"蔡先生",
    phoneNumber:"15921192867",
    adressHidden:false,
    adressChoice:true,
    open: false,
    items: [
      { name: 'money', value: '到店支付' },
      { name: 'iphone', value: '货到付款', checked: 'true' },
    ],
    goots:[
      { name: 'money', value: '厂家上门' },
      { name: 'iphone', value: '普通快递', checked: 'true' },
    ],
    address_id:0,
    address_detail:{},
    number:0,
    store_id:0,
    storeList:{},
    goods:{}
  },

  showitem: function () {
    this.setData({
      open: !this.data.open
    })

    var self = this;

    wx.getLocation({
      type: 'wgs84',
      success: function (res) {
        console.log(res);
        var latitude = res.latitude
        var longitude = res.longitude
        var speed = res.speed
        var accuracy = res.accuracy

        //获取门店的地址列表
        console.log(self.data.storeList);
        for(var index in self.data.storeList){
          if (self.data.storeList[index]['lat']){
            qqmapsdk.geocoder({
              address: self.data.storeList[index]['address'],
              success: function (res) {
                self.data.storeList[index]['lat'] = res.result.location.lat;
                self.data.storeList[index]['lng'] = res.result.location.lng;
                //同步到服务器
                common.httpP('goods/storeLocation',{},function(){

                });

              },
              fail: function (res) {
                console.log(res);
              },
              complete: function (res) {
                console.log(res);
              }
            })
          }

         
        }
      }
    })

  },
  toadress:function(){
    wx.navigateTo({
      url:"../address/address?from_=order"
    });
  },
  changeStyle:function(){
    this.setData({
      peisongHidden: false,
    })
  },
  saveStyle: function () {
    this.setData({
      peisongHidden: true,
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
    wx.getSystemInfo({
      success: function (res) {
        that.setData({
          winWidth: res.windowWidth,
          winHeight: res.windowHeight,
        });
        console.log(winHeight);
      }
    });
    common.getUserName()
    qqmapsdk = new QQMapWX({
      key: app.globalData.mapKey
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
    var data = wx.getStorageSync('order');
    var self = this;
    self.setData({
      address_id: data.address_id,
      number: data.number,
      goods: data.goods,
    });
    self.getAddress()
    self.getStore()
  },
  getAddress:function(){
    var self = this;
    var uinfo = common.getUserName();
    common.httpG('address/orderChoose',{
      address_id: self.data.address_id,
      uid: uinfo.id,
    },function(data){
      if(data.data.id){
        self.setData({
          address_id: data.data.id,
          address_detail: data.data,
        });
      }
    })

  },

  //获取门店
  getStore:function(){

    // storeList
    var self = this;
    common.httpG('goods/storeList', {
    }, function (data) {
      console.log(data)
      if (data.data) {
        self.setData({
          storeList: data.data,
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

  }
})