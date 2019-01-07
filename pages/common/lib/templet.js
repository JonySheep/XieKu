var gopay = {

  //确认订单
  gopayFn: function(e) {
    console.log(e.target.dataset)
    wx.request({
      url: getApp().globalData.globalUrl + "order/goPayOrder",
      data: {
        id: e.target.dataset.id,
        order_type: e.target.dataset.type
      },
      method: "POST",
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: function(res) {
        if (res.data.code == 200) {
          wx.setStorageSync('order_id', res.data.data.id)
          wx.setStorageSync('totomoney', res.data.data.intregral)
          wx.setStorageSync('order_list', res.data.data.goods_list)
          wx.setStorageSync('order_number', res.data.data.order_number)

          if (e.target.dataset.type == 2){
            wx.navigateTo({
              url: '../activityindent/activityindent?id=' + res.data.data.id,
            })
          } else{
            wx.navigateTo({
              url: '../xiadan/xiadan',
            })
          }

          
        } else {
          wx.showToast({
            title: res.data.err
          })
        }

      }
    })
  },

  //取消订单
  cansolorderFn: function(e, callback) {
    wx.showModal({
      title: '提示',
      content: '是否取消订单',
      success: function(res) {
        if (res.confirm) {
          wx.request({
            url: getApp().globalData.globalUrl + "order/cancelOrder",
            data: {
              id: e.target.dataset.id,
              order_type: e.target.dataset.type
            },
            method: "POST",
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success: function(res) {
              if (res.data.code == 200) {
                wx.showToast({
                  title: '取消成功',
                  duration: 2000
                })
                if (callback)                
                  callback();
              } else {
                wx.showToast({
                  title: res.data.err,
                  icon: 'none',
                  duration: 2000
                })
              }

            }
          })
        }
      }
    })
  },

  // 确认收款
  onSureOrderFn: function(e, callback) {
    var orderId = e.target.dataset.id;
    wx.showModal({
      title: '提示',
      content: '是否确认收货？',
      success: function(res) {
        if (res.confirm) {
          wx.request({
            url: getApp().globalData.globalUrl + "order/sureOrder",
            data: {
              id: orderId,
              order_type: e.target.dataset.type
            },
            method: "POST",
            header: {
              'content-type': 'application/x-www-form-urlencoded'
            },
            success: function(res) {
              if (res.data.code == 200) {
                wx.showToast({
                  title: '收货成功',
                  duration: 2000
                });
                if (callback)
                  callback();
              } else {
                wx.showToast({
                  title: res.data.err,
                  icon: 'none',
                  duration: 2000
                })
              }
            }
          })
        }
      }
    })
  },

  //查看物流
  checkorderinfoFn: function(e) {
    wx.navigateTo({
      url: '../logisticsinfo/logisticsinfo?num=' + e.target.dataset.num + '&order_type=' + e.target.dataset.type,
    })
  },

  // 申请售后 拨打售后电话
  onServerFn: function() {
    wx.request({
      url: getApp().globalData.globalUrl + "Settings/getMobile",
      header: {
        'content-type': 'application/json'
      },
      success(res) {
        if (res.data.code === 200) {
          wx.makePhoneCall({
            phoneNumber: res.data.data.mobile
          })
        } else {
          wx.showToast({
            title: res.data.err,
            icon: 'none',
            duration: 2000
          })
        }
      }
    })
  }

}
export default gopay