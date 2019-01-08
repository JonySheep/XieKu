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
    reminder: true,
    peisongHidden: true,
    name: "蔡先生",
    phoneNumber: "15921192867",
    adressHidden: false,
    adressChoice: true,
    open: false,
    opencoupan: false,
    ceshi: '',
    items: [{
        name: '1',
        value: '线上支付',
        checked: 'true'
      },
      {
        name: '2',
        value: '到店付款',
      },
    ],
    goots: [{
        name: '1',
        value: '送货上门',
        checked: 'true'
      },
      // { name: '2', value: '普通快递(仅限积分兑换礼品)', checked: 'true' },
    ],
    address_id: '',
    address_detail: {},
    number: 0,
    store_id: 0,
    stroename: '',
    storeList: {},
    goods: {},
    couponlist: [],
    order_coupom_id: '',
    order_num: '',
    order_detailedlist: [],
    shipping_way: 1, // 配送方式
    pay_integory: 1, // 支付方式
    face_money: '',
    store_show: false,
  },

  // 修改支付方式
  onPayChangeFn(e) {
    
    this.setData({
      pay_integory: e.detail.value
    });

    if (e.detail.value == 1) {
      this.setData({
        store_show: false,
        store_id: '',
        stroename: ''
      });
    } else {
      this.setData({
        store_show: true
      });
    }
  },

  // 修改配送方式
  onShippingFn(e) {
    this.setData({
      shipping_way: e.detail.value
    });
  },

  showitem: function() {
    this.setData({
      open: !this.data.open
    })
    wx.navigateTo({
      url: "../shoplist/shoplist"
    });

  },
  showcoupon: function() {
    var self = this
    self.data.opencoupan = !self.data.opencoupan
    self.setData({
      opencoupan: self.data.opencoupan
    })
  },
  toadress: function() {
    wx.navigateTo({
      url: "../address/address?from_=order"
    });
  },
  changeStyle: function() {
    this.setData({
      peisongHidden: false,
    })
  },
  saveStyle: function() {
    this.setData({
      peisongHidden: true,
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this;

    // that.getAddress()

    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          winWidth: res.windowWidth,
          winHeight: res.windowHeight,
        });
      }
    });
    common.getUserName()
    qqmapsdk = new QQMapWX({
      key: app.globalData.mapKey
    });

    that.setData({
      order_num: wx.getStorageSync('order_number'),
      order_detailedlist: wx.getStorageSync('order_list')
    })
    console.log(this.data.order_detailedlist)
    that.getcouponlistFn()
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
    var self = this;
    self.getAddress();    
    if (self.data.checkstoreinfo){
      self.setData({
        store_id: self.data.checkstoreinfo.id,
        stroename: self.data.checkstoreinfo.name
      })
    }
    
    
  },

  // 获取收货店址
  getAddress: function() {
    var self = this;
    var openid = wx.getStorageSync('openid')
    wx.request({
      url: self.data.appurl + 'address/orderChoose',
      data: {
        openid: openid,
      },
      success: function(res) {
        console.log(res);
        if (res.data.code === 200) {
          if (res.data.data) {
            self.setData({
              address_id: res.data.data.id,
              address_detail: {
                address: res.data.data.address,
                fullname: res.data.data.fullname,
                mobile: res.data.data.mobile
              }
            })
          } else {
            self.setData({
              address_id: 0
            })
          }
        } else {
          wx.showToast({
            title: res.data.err,
          })
        }
      }
    })
  },

  bindTextAreaBlur(e) {
    console.log(e.detail.value)
  },

  //获取优惠券列表
  getcouponlistFn: function() {
    var self = this;
    var o_money = wx.getStorageSync('totomoney')
    wx.request({
      url: self.data.appurl + 'users/getCoupon',
      data: {
        openid: wx.getStorageSync('openid'),
        order_money: o_money
      },
      success: function(data) {
        self.setData({
          couponlist: data.data.data
        })

      }
    })
  },

  // 选择优惠券
  usecouponFn: function(e) {
    this.setData({
      face_money: e.currentTarget.dataset.face_money,
      order_coupom_id: e.currentTarget.dataset.id,
      opencoupan: !this.data.opencoupan
    })
  },

  // 获取商品清单数据
  getorderlistFn: function() {

  },

  //确认订单
  orderFn: function() {

    var self = this
    var oid = wx.getStorageSync('openid');
    if (self.data.store_show){
      if (!self.data.store_id) {
        wx.showToast({
          title: '请选择门店',
          icon: 'none',
          duration: 2000
        })
        return;
      }
    }

    if (!self.data.address_id) {
      wx.showToast({
        title: '请填写收货地址',
        icon: 'none',
        duration: 2000
      })
      return;
    }
    
    wx.request({
      url: self.data.appurl + '/order/updateOrder',
      data: {
        id: wx.getStorageSync('order_id'),
        address: self.data.address_id,
        shipping_way: self.data.shipping_way,
        pay_integory: self.data.pay_integory,
        store_id: self.data.store_id,
        coupon_id: self.data.order_coupom_id,
        remarks: ''
      },
      success: function(data) {
        if(data.data.code == 200){
          if (data.data.data.pay_integory == 2) {
            wx.showToast({
              title: '订单创建成功！',
              success: function () {
                wx.removeStorageSync('order_id')
                wx.removeStorageSync('totomoney')
                wx.removeStorageSync('order_list')
                wx.removeStorageSync('order_number')
                wx.setStorageSync('active_id', 0)
                wx.navigateTo({
                  url: '../order/order',
                })
              }
            })
          } else {
            wx.request({
              url: self.data.appurl + '/pay/prepay',
              data: {
                openid: wx.getStorageSync('openid'),
                order_sn: data.data.data.order_sn,
                total_fee: data.data.data.total_fee
              },
              success: function (data) {
                if (data.data.code != 200) {
                  wx.showToast({
                    title: data.data.err
                  })
                  return
                }
                wx.request({
                  url: self.data.appurl + '/pay/pay',
                  data: {
                    prepay_id: data.data.data.PREPAY_ID
                  },
                  success: function (data) {
                    if (data.data.code != 200) {
                      wx.showToast({
                        title: data.data.err
                      })
                      return
                    }
                    console.log(data.data.data);
                    wx.requestPayment({
                      'timeStamp': data.data.data.timeStamp.toString(),
                      'nonceStr': data.data.data.nonceStr,
                      'package': data.data.data.package,
                      'signType': data.data.data.signType,
                      'paySign': data.data.data.paySign,
                      'success': function (data) {
                        wx.removeStorageSync('order_id')
                        wx.removeStorageSync('totomoney')
                        wx.removeStorageSync('order_list')
                        wx.removeStorageSync('order_number')
                        wx.setStorageSync('active_id', 1)
                        wx.navigateTo({
                          url: '../order/order',
                        })
                      },
                      'fail': function (res) {
                        wx.showToast({
                          title: '支付已取消'
                        })
                      }
                    })
                  }
                })
              }
            })
          }
        } else {
          wx.showToast({
            title: data.data.err,
            icon: 'none',
            duration: 2000
          })
        }
      }
    })

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