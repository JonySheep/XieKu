// pages/shoppingcar/shoppingcar.js
var custShowToast = require('../../utils/custshowtoast'); //引入消息提醒暴露
var d = 1;
var app = getApp();
var WxParse = require('../../wxParse/wxParse.js');
var common = require("../../utils/util.js");
Page({

  /**
   * 页面的初始数据
   */
  data: {
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    shoplist:[],
    checkedList: [],
    totalMoney: 0,
    isAllSelect: false
      
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.setData({
      checkedList:[],
      totalMoney: 0
    })
  },

  jian: function (e) {
    var self = this;
    if (e.currentTarget.dataset.num > 1) {
      var index_n = e.currentTarget.dataset.index
      console.log(self.data.shoplist[index_n].goods_num)
      var num = self.data.shoplist[index_n].goods_num
      var price = self.data.shoplist[index_n].price
      num = num - 1
      self.data.shoplist[index_n].goods_num = num
      self.data.shoplist[index_n].allprice = price * num
      self.getchecklsit()
      self.priceFn()
      self.setData({
        shoplist: self.data.shoplist,
      });
      wx.request({
        url: app.globalData.globalUrl + "cart/setDecCart", 
        data: {
          id: e.currentTarget.dataset.id,
        },
        method: "POST",
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          if (res.data.code == 200) {
            app.globalData.isaddcar = true
            app.changecarNumFn()

          } else {
            wx.showToast({
              title: res.data.err
            })
          }

        }
      })
    }
  },

  jia: function (e) {
    var self = this
    var index_n = e.currentTarget.dataset.index
    var num = self.data.shoplist[index_n].goods_num
    var price = self.data.shoplist[index_n].price
    num = num+1
    self.data.shoplist[index_n].goods_num =num
    self.data.shoplist[index_n].allprice = price*num
    self.getchecklsit()
    self.priceFn()
    self.setData({
      shoplist: self.data.shoplist,    
    });

    wx.request({
      url: app.globalData.globalUrl + "cart/setIntCart",
      data: {
        id: e.currentTarget.dataset.id,
      },
      method: "POST",
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        if (res.data.code == 200) {
          app.globalData.isaddcar = true
          app.changecarNumFn()

        } else {
          wx.showToast({
            title: res.data.err
          })
        }

      }
    })

  },

  // 单选
  switchSelect: function(e){
    var self = this
    var Allprice = 0, i = 0;
    let index = e.currentTarget.dataset.index
    if (self.data.shoplist[index].status == 0){
      self.data.shoplist[index].status = 1
    } else if (self.data.shoplist[index].status == 1){
      self.data.shoplist[index].status = 0
    }
    self.getchecklsit();
    if (self.data.checkedList.length == self.data.shoplist.length){
      self.data.isAllSelect = true
    } else{
      self.data.isAllSelect = false
    }
    self.priceFn();
    self.setData({
      shoplist: self.data.shoplist,
      isAllSelect: self.data.isAllSelect,
      totalMoney: self.data.totalMoney
    })


  },

  // 全选
  allSelect: function (e) {
    var self = this
    self.data.isAllSelect = !self.data.isAllSelect
    let i = 0;  
    if (self.data.isAllSelect) {
      for (i = 0; i < self.data.shoplist.length; i++) {
        self.data.shoplist[i].status = 1;      
      }
    }else {
      for (i = 0; i < self.data.shoplist.length; i++) {
        self.data.shoplist[i].status = 0;
      }    
    }
    self.getchecklsit()
    self.priceFn()
    self.setData({
      shoplist: self.data.shoplist,
      isAllSelect: self.data.isAllSelect,
      totalMoney: self.data.totalMoney,
    })
  },

  // 价格计算
  priceFn: function(){
    var self = this
    var tatolp = 0
    self.data.checkedList.forEach(data => {
      tatolp += data.price * data.goods_num
    })
    self.setData({
      totalMoney: tatolp
    })
  },

  // 获取选种item
  getchecklsit: function(){
    var self = this
    var c_arr = []
    self.data.shoplist.forEach(data => {
      if (data.status == 1) {
        c_arr.push(data)
      }
    })
    self.data.checkedList = c_arr
  },

  // 移除购物车
  onRemoveFn(){
    var self = this
    if (self.data.checkedList.length > 0) {
      var carid_str = []
      var cart_json = []
      var bo_cart = {}
      self.data.checkedList.forEach(data => {
        bo_cart = { goods_id: data.goods_id, goods_num: data.goods_num }
        carid_str.push(data.id)
        cart_json.push(bo_cart)
      })
      wx.request({
        url: app.globalData.globalUrl + "cart/delCart",
        data: {
          ids: carid_str.toString(),
        },
        method: "POST",
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          if (res.data.code == 200) {
            wx.showToast({
              title: '移除成功'
            })
            self.setData({
              totalMoney: 0,
              checkedList: []
            })
            
            wx.request({ //购物车列表
              url: app.globalData.globalUrl + "/cart/cartList",
              data: {
                openid: wx.getStorageSync('openid')
              },
              method: "POST",
              header: {
                'content-type': 'application/x-www-form-urlencoded'
              },
              success: function (res) {
                self.setData({
                  shoplist: res.data.data,
                })
                self.data.shoplist.forEach(item => {
                  item.allprice = ''
                })
                app.globalData.isaddcar = true
                app.changecarNumFn()
              }
            })
          } else {
            wx.showToast({
              title: res.data.err
            })
          }

        }
      })
    } else {
      wx.showToast({
        title: '请选择商品',
        icon: ''
      })
    }
  },

  // 去结算
  overpayFn: function(){
    var self = this
    if (self.data.checkedList.length>0){
      var carid_str = []
      var cart_json = []
      var bo_cart = {}
      self.data.checkedList.forEach(data => {
        bo_cart = { goods_id: data.goods_id, goods_num: data.goods_num }
        carid_str.push(data.id)
        cart_json.push(bo_cart)
      })
      wx.request({
        url: app.globalData.globalUrl + "cart/cartToOrder",
        data: {
          openid: wx.getStorageSync('openid'),
          cart_ids: carid_str.toString(),
          cart_json: JSON.stringify(cart_json)
        },
        method: "POST",
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          console.log(res)
          if(res.data.code == 200){
            self.setData({
              checkedList:[],
              totalMoney: 0
            })
            wx.setStorageSync('order_id', res.data.data.id)
            wx.setStorageSync('totomoney', res.data.data.intregral)
            wx.setStorageSync('order_list', res.data.data.goods_list)  
            wx.setStorageSync('order_number', res.data.data.order_number) 

            app.globalData.isaddcar = true
            app.changecarNumFn()

            wx.navigateTo({
              url: '../xiadan/xiadan',
            })
          } else {
            wx.showToast({
              title: res.data.err
            })
          }

        }
      })
    } else {
      wx.showToast({
        title: '请选择商品',
        icon: ''
      })
    }    
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
    var self = this

    app.globalData.isaddcar = true
    app.changecarNumFn()

    wx.request({ //购物车列表
      url: app.globalData.globalUrl + "/cart/cartList",
      data: {
        openid: wx.getStorageSync('openid')
      },
      method: "POST",
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        self.setData({
          shoplist: res.data.data,
          isAllSelect: false,
          totalMoney: 0,
        })
        self.data.shoplist.forEach(item => {
          item.allprice = ''
        })
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