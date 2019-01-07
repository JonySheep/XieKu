// pages/gainintegral/gainintegral.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    appurl: getApp().globalData.globalUrl,
    imgUrl: getApp().globalData.globalStaticUrl,
    currentTab: 0,
    topTab: 0,
    topList: [],
    obj1: {
      img1: "https://www.swahouse.com/index.php/image/welfare7.png",
      title1: "ONG 冷凝系列***",
      score1: 666,
      img2: "https://www.swahouse.com/index.php/image/welfare7.png",
      title2: "ONG 冷凝系列***",
      score2: 666,

    },
    category: {},
    leftgory: [],
    isshowmodel:false,
    shareId:'',
    goods_id:'',
  },

  swichtopNav: function (event) {
    var slef = this
    slef.setData({
      topTab: event.currentTarget.dataset.current
    })
    slef.rightlistFn(event.currentTarget.dataset.lid)

    slef.data.topList.forEach(data => {

      if (data.id == event.currentTarget.dataset.lid){
        slef.setData({
          leftgory: data.sub_menu
        })
      }
      
    })

  },

  swichNav: function (event) {
    this.setData({
      currentTab: event.currentTarget.dataset.current
    })
    
  },


  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideLoading()
    var self = this;

    // 头部菜单
    wx.request({
      url: self.data.appurl + 'share/shareCategory',
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        self.setData({
          topList: res.data.data
        });
        self.rightlistFn(self.data.topTab+1)
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
  onShareAppMessage: function (res) {
    var that = this;
    that.setData({
      isshowmodel: false
    });
    var goods_path_params = 'pages/productdetails/productdetails?id=' + that.data.goods_id + '&share_token=' +that.data.shareId;
    if (res.from === 'button') {
      // 来自页面内转发按钮
      return {
        title: '斜厍',
        path: goods_path_params,
        success: function (res) {// 转发成功      
          console.log("转发成功:" + JSON.stringify(res));
          wx.showToast({
            title: '分享成功！'
          })
        }
      }
    }
  },

  // 获取用户分享参数
  getShareUrl: function (ele){
    var openid = wx.getStorageSync('openid');
    var goods_id = ele.target.dataset.id;
    var self = this;
    wx.request({
      url: self.data.appurl + 'Share/getShareUrl',
      header: {
        'content-type': 'application/json'
      },
      data:{
        openid: openid,
        goods_id: goods_id,
      },
      success: function (res) {
        if (res.data.code == 200){
          self.setData({
            isshowmodel: true,
            shareId: res.data.data.share_token,
            goods_id: res.data.data.goods_id,
          });
        }
      }
    })
  },

  // 关闭分享窗口
  closeModelFn:function(){
    this.setData({
      isshowmodel: false
    });  
  },

  // 商品列表
  rightlistFn: function(id){
    var self = this
    // 内容
    wx.request({
      url: self.data.appurl + 'share/shareList',
      data: {
        id: id
      },
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

  //点击分享
  shareFn: function(e){
    console.log(e)  
  }
})