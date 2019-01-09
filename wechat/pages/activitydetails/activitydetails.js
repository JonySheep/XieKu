// pages/activitydetails/activitydetails.js
var WxParse = require('../../wxParse/wxParse.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
      activityBanner : [], //活动banner
      activitydetails: [], //活动详情
      collectTxt     : 0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      var that = this;
      var activityId = options.id
      if( ! activityId )
      {
        return false
      }
      // 获取系统信息   
     /* wx.getSystemInfo({
        success: function (res) {
          that.setData({
            winWidth: res.windowWidth,
            winHeight: res.windowHeight
          });
        }
      }); */
      that.setData({ activityId : activityId })
      //获取活动详情
      that.getActivityFind(activityId)
      //浏览+
      that.addActivityNumnber(activityId)
       
  },
  //添加浏览量
  addActivityNumnber : function(activityId)
  {
      wx.request({
          url   : 'https://www.swahouse.com/index.php/api/Activity/addActivityNumnber',
          data  : {
              id : activityId
          },
          success : function(rsf)
          { 
              console.log(rsf)
          }
      })

      console.log(activityId)
  },
  //获取单个活动
  getActivityFind : function(activityId)
  {   
      var that = this
      var userInfo = wx.getStorageSync('userinfo')
      var usersId  = userInfo.id ? userInfo.id : 0
      console.log(usersId)
      wx.request({
          url  : 'https://www.swahouse.com/index.php/api/Activity/getActivityFind',
          data : {
              id        : activityId,
              users_id  : usersId
          },
          success : function(rsf)
          {
              console.log(rsf)
              if( rsf.data.code === 400 )
              {
                  wx.showToase({
                      title : rsf.data.err,
                      icon: 'none',
                      duration: 2000
                  })
              }else{

                  var article = rsf.data.data['particulars'];
                  WxParse.wxParse('article', 'html', article, that);

                  that.setData({
                      activitydetails : rsf.data.data,
                      collectTxt      : rsf.data.data.isCollect
                  })
              } 
              //console.log(that.data.activitydetails)
          }
      })
  },
  //获取活动banner
  getActivityBanner : function()
  { 
      wx.request({
          url  : 'https://www.swahouse.com/index.php/api/Activity/getActivityBanner',
          success : function(rsf)
          {
              console.log(rsf)
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
      return {
        title: this.data.activitydetails.activity_name,
        path: '/pages/activitydetails/activitydetails?id='+this.data.activitydetails.id,
        success: (res) => {
          console.log("转发成功", res);
        },
        fail: (res) => {
          console.log("转发失败", res);
        }
      }
  },
  //跳转首页
  redirectHome : function()
  {   

      wx.switchTab({
          url   : '../home/home',
          fail  : function(e)
          {
            console.log(e)
          }
      })
  },
  //活动收藏
  activityCollect : function()
  {   
      var openId = wx.getStorageSync('openid')
      var activityId = this.data.activityId
      var that = this
      if( ! openId )
      {
          wx.switchTab({
              url   : '../center/center',
              fail  : function(e)
              {
                console.log(e)
              }
          })
      }else{
          wx.request({
              url   : 'https://www.swahouse.com/index.php/api/Activity/activityCollect',
              data  : {
                  activity_id : activityId,
                  open_id     : openId
              },
              success : function(rsf)
              {
                  if( rsf.data.code === 200 )
                  { 
                    console.log('aaa')
                      if(that.data.collectTxt === 0) {
                        that.setData({ collectTxt: 1 })
                      } else {
                        that.setData({ collectTxt: 0 })
                      }
                      console.log(that.data.collectTxt)
                  }else{
                      wx.showToast({
                          title : rsf.data.err,
                          icon: 'none',
                          duration: 2000
                      })
                  }
              }
          })
      }
  },
  activityConversion : function()
  {   
      var openId = wx.getStorageSync('openid')
      if( !openId )
      {
         wx.switchTab({
              url   : '../center/center',
              fail  : function(e)
              {
                console.log(e)
              }
          })
       }else{
          var that = this
          var openid = wx.getStorageSync('openid')
          var userInfo = wx.getStorageSync('userinfo')
          var usersId  = userInfo.id ? userInfo.id : 0
          var activityId  = that.data.activitydetails.id
          wx.request({
            url: 'https://www.swahouse.com/index.php/api/order_act/createOrder',
              data : {
                  activity_id   : activityId,
                  openid: openid
              },
              success : function(rsf)
              {   
                  if( rsf.data.code === 200 )
                  {

                    wx.navigateTo({
                      url: '../activityindent/activityindent?id=' + rsf.data.data.id + '&order_number=' + rsf.data.data.order_number
                    })
                      // wx.showToast({
                      //     title: '兑换成功',
                      //     icon: 'succes',
                      //     duration: 1000,
                      //     mask:true
                      // })
                  }else{
                      wx.showToast({
                          title : rsf.data.err,
                          icon: 'none',
                          duration: 2000
                      })
                  }
              }
          })
       }
  }
})