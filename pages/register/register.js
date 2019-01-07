Page({

  /**
   * 页面的初始数据
   */
  data: {
    tel:''
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log(options)
      //获取用户手机号码判断是否绑定
      if( ! options.change )
      {
          wx.request({
              url  : 'https://www.swahouse.com/index.php/api/Users/getUsersTel',
              data : {
                  'open_id' : wx.getStorageSync('openid')
              },
              success : function(rsf)
              { 
                  var tel = rsf.data.data.rsf
                  console.log(tel)
                  if( tel )
                  {
                      wx.navigateTo({
                          url: '../registerSuccess/registerSuccess'
                      })
                  }
              }
          })
      }      
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    
  }, 
  onLaunch: function () {
   
  },
  telNumber: function (e) {
    this.setData({
      tel: e.detail.value
    })

  },
   //授权
  getPhoneNumber: function(e) {      
    if (e.detail.errMsg == 'getPhoneNumber:fail user deny'){ 
        wx.showModal({ 
           title: '提示', 
           showCancel: false, 
           content: '未授权', 
           success: function (res) { 

           } 
       }) 
    } else { 
        console.log(e.detail.iv)  
        console.log(e.detail.encryptedData)  
        console.log( wx.getStorageSync('session_key') )
        var that = this
        wx.showModal({ 
           title: '提示', 
           showCancel: false, 
           content: '同意授权', 
           success: function (res) {             
              //发起请求
              wx.request({
                url  : 'https://www.swahouse.com/index.php/api/Users/getTel',
                data : {
                    sessionKey : wx.getStorageSync('session_key'),
                    encryptedData : e.detail.encryptedData,
                    iv   : e.detail.iv
                },
                header: {
                  'content-type': 'application/json'
                },
                success : function(rsf)
                {
                    console.log(rsf.data.data)
                    that.setData({
                        tel : rsf.data.data.phoneNumber
                    })
                }
              })
           } 
        }) 
    } 
  },
  shouquan:function(e){   
    wx.setStorageSync('tel', this.data.tel);
    var openid = wx.getStorageSync('openid');
    console.log(openid + "    openid   "+this.data.tel+"   number");
    var that = this;
    wx.request({
      url: 'https://www.swahouse.com/index.php/api/Users/setTel',
      data: {
        openId: openid,
        tel:that.data.tel
      },
      header: {
        'content-type': 'application/json'
      },
      success: function (res) {
        console.log(JSON.stringify(res) + "    电话");
        if(res.data.code == 200){
          wx.navigateTo({
            url: '../registerSuccess/registerSuccess'
          })
        }else{
         // console.log('_++++++++++')
            console.log(res.data)
            wx.showToast({
              title: res.data.err,
              icon: 'none',
              duration: 2000
            })
        }
      }
    })
    
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
  onShareAppMessage: function () {
    
  }
})