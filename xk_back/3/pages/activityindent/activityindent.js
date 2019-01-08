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
    address : [],
    winHeight: 0,
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    reminder:true,
    peisongHidden:true,
    adressHidden:false,
    adressChoice:true,
    open: false,
    address_id:0,
    address_detail:{},
    number:0,
    store_id:0,
    storeList:{},
    goods:{},
    addressInfo : [], //地址信息
    IndentDetails : [],  //订单详情
    remark        : '',
    users_activity_conversion_id  : '', //活动订单id
    is_first : 0 //是否第一次点击
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
      var that = this
      var userInfo = wx.getStorageSync('userinfo')
      var usersId  = userInfo.id
      //获取用户地址
      that.getUsersSite(usersId)  
      //参加的活动订单id
      var activityId = options.id ? options.id : 26
      that.getActivityFind(activityId)
      //获取订单详情
      that.getIndentDetails(activityId)
      //设置活动订单ID
      that.setData({
          users_activity_conversion_id : activityId
      })
  },
  //获取用户默认地址
  getUsersSite : function(UsersId)
  {   
      var that   = this  
      wx.request({
          url   : 'https://www.swahouse.com/api/Users/getUsersSite',
          data  : {
             users_id : UsersId
          },
          success : function(rsf)
          { 
              that.setData({
                  address : rsf.data.data
              })
              console.log('用户默认地址')
              console.log(that.data.address)
          }
      });
  },
  //获取活动商品详情
  getActivityFind : function(activityId,usersId)
  {   
      var that = this 
      wx.request({
           url  : 'https://www.swahouse.com/api/Activity/getCActivity',
           data : {
              activity_id : activityId,
           },
           success : function(rsf)
           {  
                console.log('活动商品详情')
                console.log(rsf.data.data)
                that.setData({
                    goods : rsf.data.data
                })
           }
      })
  },
  //获取订单详情
  getIndentDetails : function(activityId)
  {
      var that = this 
      wx.request({
           url  : 'https://www.swahouse.com/api/Activity/getIndentDetails',
           data : {
              activity_id : activityId,
           },
           success : function(rsf)
           {  
                console.log('订单详情')
                console.log(rsf.data.data)
                that.setData({
                    IndentDetails : rsf.data.data
                })
           }
      })
  },
  //提交订单
  sublimeIndent:function()
  {
      var that = this
      var is_first = that.data.is_first
      if( is_first === 0 )
      { 
        that.setData({
            is_first  : 1
        })
        return false
      }
      wx.request({
           url  : 'https://www.swahouse.com/api/Activity/sublimeIndent',
           data : {
              users_site_id : that.data.address.id,
              remark        : that.data.remark,
              users_activity_conversion_id : that.data.users_activity_conversion_id
           },
           success : function(rsf)
           {  
              console.log('提交订单')
              console.log(rsf)
                if( rsf.data.code === 200 )
                {
                  console.log('提交成功')
                }else{
                  wx.showToast({
                    title: rsf.data.err,
                    icon: 'none',
                    duration: 2000
                  })              
                }       
           }
      })
  },
  //获取订单备注
  bindTextAreaBlur : function(e)
  {
      this.setData({
           remark : e.detail.value
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
   /* var data = wx.getStorageSync('order');
    var self = this;
    self.setData({
      address_id: data.address_id,
      number: data.number,
      goods: data.goods,
    });
    self.getAddress()
    self.getStore()*/
  },
/*  getAddress:function(){
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

  },*/

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