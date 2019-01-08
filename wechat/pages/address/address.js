// pages/address/address.js
var common = require("../../utils/util.js");
var app = getApp();
const imgurl = app.globalData.imgUrl;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    appurl: getApp().globalData.globalUrl,
    imgurl: imgurl,
    addressList: [],
    from_: '', //从哪个页面来，默认 为空是从我的地址管理
    newAddHide: false,
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    var that = this 
    //获取地址列表
    that.getAddressList();
  },

  //添加收货地址
  addRess : function()
  {
      wx.navigateTo({
          url   : '../newAdd/newAdd'
      })

  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
     
  },

  //取地址列表
  getAddressList: function() 
  {
      var that = this;
      var openid = wx.getStorageSync('openid');
      wx.request({
        url: app.globalData.globalUrl + "address/addList",
          data  : {
            openid: openid
          },
          success : function(res)
          {   
            if(res.data.code === 200){
                  that.setData({
                    addressList: res.data.data
                  })        
            }else{
              wx.showToast({
                title: res.err,
              })
            }
          }
      })
  },

  // 一键获取收货地址
  onAutoFn(){
    wx.chooseAddress({
      success: function(res){
        console.log(res);
        var openid = wx.getStorageSync('openid')
        wx.request({
          url: app.globalData.globalUrl + 'address/add',
          data: {
            openid: openid,
            fullname: res.userName,
            mobile: res.telNumber,
            address: res.provinceName + res.cityName + res.countyName + res.detailInfo,
            postal: res.postcode,
            is_default: 0,
            id: ''
          },
          method: 'POST',
          header: {
            'content-type': 'application/x-www-form-urlencoded'
          },
          success: function (rsf) {
            if (rsf.data.code === 400) {
              wx.showToast({
                title: rsf.data.err
              })
            } else {
              wx.showToast({
                title: '一键获取地址成功',
                success: function () {
                  // wx.navigateBack();
                }
              });

            }
          }
        })
      }
    })
  },

  //设为默认地址
  tapSetDefault: function(e) {
    var that = this;
    var address_id = e.target.dataset.address_id;
    var openid = wx.getStorageSync('openid');
    wx.request({
      url: that.data.appurl + 'address/choose',
      method: 'POST',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      data:{
        openid: openid,
        id: address_id
      },
      success: function (res) {
        if(res.data.code === 200){
          wx.navigateBack();
        }else{
          wx.showToast({
            title: res.data.err
          })
        }
      }
    });
  },

  //编辑地址
  tapEditAddress: function(e) {
    var that = this;
    var address_id = e.target.dataset.address_id;
    wx.navigateTo({
      url: '/pages/newAdd/newAdd?address_id=' + address_id,
    })
  },

  orderChoose:function(e){
    var that = this;
    var address_id = e.currentTarget.dataset.id;
    var openid = wx.getStorageSync('openid');
    wx.request({
      url: that.data.appurl + 'address/choose',
      method: 'POST',
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      data: {
        openid: openid,
        id: address_id
      },
      success: function (res) {
        if (res.data.code === 200) {
          wx.navigateBack();
        } else {
          wx.showToast({
            title: res.data.err
          })
        }
      }
    });
  },

  //删除地址
  tapDelAddress: function(e) {
    var that = this;
    var address_id = e.target.dataset.address_id;
    var openid = wx.getStorageSync('openid');
    wx.showModal({
      title: '删除',
      content: '确认删除么？',
      success: function(res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.globalUrl + "address/delete",
            data: {
              openid: openid,
              id: address_id
            },
            method: 'POST',
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success: function (res) {
              if (res.data.code === 200) {
                that.getAddressList();
              } else {
                wx.showToast({
                  title: res.err,
                })
              }
            }
          })
        } else if (res.cancel) {
          return;
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