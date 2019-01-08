//logs.js
const util = require('../../utils/util.js')

Page({
  data: {
    integral : 0,
    imgUrls: [],
    indicatorDots: true,
    autoplay:true,
    interval: 5000,
    duration: 1000,
    currentTab:0,
    //模板数据
    ceshi:[],
    ceshi1 : [],
    ceshi2 : []
  },
  
  onLoad: function () {
    this.setData({
      logs: (wx.getStorageSync('logs') || []).map(log => {
        return util.formatTime(new Date(log))
      })
    })
  },
  //获取banner
  getBanner : function()
  {   
      var that = this
      wx.request({
          url : 'https://www.swahouse.com/index.php/api/Activity/getActivityBanner',
          header: {
            'content-type': 'application/json'
          },
          success : function(ref)
          {
            // console.log(ref)
             that.setData({
                imgUrls : ref.data.data
             })
          }
      });
  },
  //获取活动
  getActivity : function(genre)
  {   
      var that = this
      wx.request({
          url : 'https://www.swahouse.com/index.php/api/Activity/getActivity',
          data : {
              genre : genre
          },
          success : function(rsf)
          {
              //var ceshi = genre == 1 ? 'ceshi' : (genre == 2 ? 'ceshi1' : 'ceshi2' );
              var data  = rsf.data.data    
              var lg    = data.length
              
              for (var i = 0; i < lg;i++){
                console.log(data[i].residueTime)
                  data[i].residue_time = that.hms(data[i].residueTime,1)
              }
              //如果是进行中的活动
              if( genre === 1 )
              {
                  that.setData({
                      ceshi : data
                  })
              }
              //即将开始的活动
              if( genre === 2  )
              {
                  that.setData({
                      ceshi1 : data
                  })
              }
              //已经结束的活动
              if( genre === 3 )
              {
                  that.setData({
                      ceshi2 : data
                  })
              }  
              //开始倒计时
              var step  = 1//步进值
              setInterval(function () {               
                  for (var i = 0; i < lg;i++){
                      data[i].residue_time = that.hms(data[i].residueTime-step,1)
                  }
                  //如果是进行中的活动
                  if( genre === 1 )
                  {
                      that.setData({
                          ceshi : data
                      })
                  }
                  //即将开始的活动
                  if( genre === 2  )
                  {
                      that.setData({
                          ceshi1 : data
                      })
                  }
                  //已经结束的活动
                  if( genre === 3 )
                  {
                      that.setData({
                          ceshi2 : data
                      })
                  }  
                  step ++
              }, 1000)
          }
      })
  },
  //活动数据转换
  hms : function(time,genre)
  {   
      //天
     /* var d = Math.floor(time / (3600*24))
      time  = time - (d*(3600*24))*/
      //时
      var h = Math.floor(time / 3600)   
      //分钟
      time = time - (h*3600)
      var m = Math.floor( time / 60 )
      //秒
      var s = time - (m*60)
   
      if (time>0)     
          return /*d+':'+*/h + ":" + m + ":"+s;       
      else return "已结束";            
  },
  
  toCoupons: function () {
    wx.navigateTo({
      url: '../coupons/coupons',
    })
  },
  bindChange: function (e) {
    var that = this;
    that.setData({ currentTab: e.detail.current,  });
  },
  /** 
   * 点击tab切换 
   */
  swichNav: function (e) {
    //console.log(e)
    var that = this;
    if (this.data.currentTab === e.target.dataset.current) {
      return false;
    } else {
        that.setData({
          currentTab: e.target.dataset.current,
        })
        //开始加载活动    
        that.getActivity( parseInt(e.target.dataset.current) + 1  )
    }
  },
  //页面载入
  onShow : function()
  { 
      wx.hideLoading()
      var usersInfo =  wx.getStorageSync('userinfo')
      var integral = usersInfo ? usersInfo.integral : 0  
      this.setData({
          integral : integral
      })
      //获取banner
      this.getBanner()
      //获取活动
      this.getActivity(1)

    app.globalData.isaddcar = true
    app.changecarNumFn()

  },
  dhjl : function()
  {
      wx.navigateTo({
        url: '../score/score',
      })
  },
  toShopCenter : function() {
    wx.switchTab({
      url: '../home/home',
    })
  },
  //特惠套餐
  preferenceCombo : function()
  {
      // wx.showToast({
      //     title : '敬请期待......',
      //     icon: 'none',
      //     duration: 2000
      // })
    wx.navigateTo({
      url: '../gainintegral/gainintegral',
    })
    
  },
  //获取详情跳转
  activitydetails : function(e)
  {   
      var id = e.target.dataset.current
      console.log(id)
      wx.navigateTo({
          url: '../activitydetails/activitydetails?id='+id,
      })
  },
  //活动兑换
  activityConversion : function(e)
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
          console.log(e)
          var that = this
          var openid = wx.getStorageSync('openid');
          var activityId  = e.target.dataset.current
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
