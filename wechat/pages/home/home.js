// pages/home/home.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    hotList: {},
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    brands: {},
    categories: {},
    category_cur: 0,
    category_products: {},
    banners:{},
    userInfo:{},
    hasUserInfo:false
  },

  gotulogsFn(e){
    wx.navigateTo({
      url: '../categorydetails/categorydetails?id=' + e.currentTarget.dataset.categoryid,
    })
  },

  bannerTo(e){
    wx.navigateTo({
      url: '../webview/webview?type=banner&url=' + e.currentTarget.dataset.url,
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {

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
    wx.hideLoading()
    var self = this;
    // wx.clearStorage();
    var openid = wx.getStorageSync('openid');
    if(!openid){
      wx.switchTab({
        url: '../center/center',
        fail: function (e) {
          console.log(e)
        }
      });
      return ;
    }
    //品牌
    wx.request({
      url: self.data.appurl + 'brand',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        self.setData({
          brands: res.data.data
        });
      }
    })
    //分类产品
    wx.request({
      url: self.data.appurl + 'category/products',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        self.setData({
          categories: res.data.data
        });

        if (res.data.data[0]['goods']) {
          self.setData({
            category_products: res.data.data[0]['goods']
          });
        }

      }
    });
    //热销产品
    wx.request({
      url: self.data.appurl + 'goods',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        self.setData({
          hotList: res.data.data
        });

      }
    });
    //banner
    wx.request({
      url: self.data.appurl + 'banner?type=3',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        self.setData({
          banners: res.data.data
        });

      }
    });

    app.globalData.isaddcar = true
    app.changecarNumFn()
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

  },
  //点击类目
  choose_category: function(event) {
    var index = event.currentTarget.dataset.cur;
    this.setData({
      category_cur: index
    })

    var self = this;
    if (self.data['categories'][index]['goods']) {

      self.setData({
        category_products: self.data['categories'][index]['goods']
      });
    }
  },
  toGoodsDetail: function(event) {
    wx.navigateTo({
      url: '../productdetails/productdetails?id=' + event.currentTarget.dataset.goodsid,
    })
  },

  // 跳转到购物车页面
  // goshoppingcarFn: function(){
  //   wx.navigateTo({
  //     url: '../shoppingcar/shoppingcar'
  //   })
  // },

  //搜索
  search: function (e) {
    //获取输入的值
    var search = e.detail.value
    var that = this
    if(search)
    {
      //发送请求
      wx.request({
        url: 'https://www.swahouse.com/index.php/api/Search',
        data: {
          name: search
        },
        header: {
          'content-type': 'application/json'
        },
        success: function (res) {
          that.setData({
            val:1
          })
        },
        fail:function(err) {
          console.log(0)
        }
      })
    }
  },
})