function fetch(options) {
  // console.log(options,'optios');
  let header = {
    "Content-Type": "application/json",
  };
  return new Promise((resolve, reject) => {
    const instance = axios.create({
      //instance鍒涘缓涓€涓猘xios瀹炰緥锛屽彲浠ヨ嚜瀹氫箟閰嶇疆锛屽彲鍦� axios鏂囨。涓煡鐪嬭鎯�
      //鎵€鏈夌殑璇锋眰閮戒細甯︿笂杩欎簺閰嶇疆锛屾瘮濡傚叏灞€閮借鐢ㄧ殑韬唤淇℃伅绛夈€�
      headers: header,
      timeout: 20 * 1000, // 30绉掕秴鏃�
    });
    instance.interceptors.request.use((config) => {
      if (config.method == "post") {
        config.headers = {
          "Content-Type": "application/x-www-form-urlencoded",
        };
      }
      return config;
    });
    instance(options)
      .then((response) => {
        resolve(response);
      })
      .catch((error) => {
        console.log("璇锋眰寮傚父淇℃伅锛�" + error);
        reject(error);
      });
  });
}

// const baseUrl = "http://192.168.31.130"
const baseUrl='https://api.gxhuimu.cn'
const URL ='http://pay.gxhuimu.cn'
const URLS ='https://pay.gxhuimu.com'
// const URLS ='http://192.168.31.130:9216'
function Url() {
  return baseUrl;
}
// 鍥涙柟鏀粯
function apiPay(params){
  return fetch({
    url:URL +'/api/pay/getByCode',
    method:'post',
    data:qs.stringify(params)
  })
}
function yzfOrder(params){
  return fetch({
    url:URL +'/api/pay/yzfOrder',
    method:'post',
    data:qs.stringify(params)
  })
}
// 寰俊璺宠浆灏忕▼搴忔敮浠�
function acc(params){
  return fetch({
    url:baseUrl +'/acc/getByCode',
    method:'post',
    data:qs.stringify(params)
  })
}
function accSf(params){
  return fetch({
    url:URLS + '/api/pay/getByWxCode',
    method:'post',
    data:qs.stringify(params)
  })
}
// 鏀粯瀹�2鍙�
function accSfzfb(params){
  return fetch({
    url:URLS + '/api/pay/HjZfb',
    method:'post',
    data:qs.stringify(params)
  })
}
// 鏀粯瀹�2022/01/27
function BYCode(params){
  return fetch({
    url:baseUrl +'/charge/getByCode',
    method:'post',
    data:qs.stringify(params)
  })
}
function hmpayAli(params){
  return fetch({
    url:baseUrl +'/charge/hmpayAli',
    method:'post',
    data:qs.stringify(params)
  })
}
// 鐢ㄦ埛鍏虫敞涓庡彇娑�
function like(params){
  return fetch({
    url:baseUrl +'/fans/v1/likeH5',
    method:'post',
    data:qs.stringify(params)
  })
}
function Like(params){
  console.log(params,'parmas');
  return fetch({
    url:baseUrl +'/fans/v2/likeH5',
    method:'post',
    data:qs.stringify(params)
  })
}
//鏈€鍚庝竴绉掓父鎴忚秴绾у垢杩愭槦棰嗗彇
function drawBox(params) {
  return fetch({
    url:  baseUrl + "/userGuess/drawBox",
    method: "post",
    data: qs.stringify(params),
  });
}
//鏈€鍚庝竴绉掓父鎴忕敤鎴疯喘涔扮珵鐚滄父鎴忕ぜ鐗�
function buyGuessGift(params) {
  return fetch({
    url:  baseUrl + "/userGuess/buyGuessGift",
    method: "post",
    data: qs.stringify(params),
  });
}
//鏈€鍚庝竴绉掓父鎴忎笅娉�
function userGuessJoin(params) {
  return fetch({
    url:  baseUrl + "/userGuess/join",
    method: "post",
    data: qs.stringify(params),
  });
}
//鏈€鍚庝竴绉掕幏鍙栫敤鎴疯幏鑳滆褰�
function listGuessWinRecord(params) {
  return fetch({
    url:  baseUrl + "/userGuess/listGuessWinRecord",
    method: "post",
    data: qs.stringify(params),
  });
}
//鏈€鍚庝竴绉掕幏鍙栫敤鎴蜂笅娉ㄨ褰�
function listGuessJoinRecord(params) {
  return fetch({
    url:  baseUrl + "/userGuess/listGuessJoinRecord",
    method: "post",
    data: qs.stringify(params),
  });
}
//鏈€鍚庝竴绉掔ぜ鐗╀俊鎭互鍙婅儗鍖呯ぜ鐗�
function getUserGiftPurse(params) {
  return fetch({
    url:  baseUrl + "/userGuess/getUserGiftPurse",
    method: "post",
    data: qs.stringify(params),
  });
}
//鏈€鍚庝竴绉掓父鎴忎俊鎭�
function userGuessInfo(params) {
  return fetch({
    url:  baseUrl + "/userGuess/guessInfo",
    method: "get",
    params
  });
}
//鏉夊痉鏀粯
function chargeSandpayApply(val) {
  return fetch({
    url: baseUrl + "/charge/sandpay/apply",
    method: "post",
    data: qs.stringify(val),
  });
}
//涓冨濂崇
function activityEndResult(params) {
  return fetch({
    url:  baseUrl + "/activityGift/rank/endResult",
    method: "get",
    params
  });
}
//鏀跺埌娲诲姩绀肩墿鎺掕鍒楄〃
function activityGiftList(params) {
  return fetch({
    url:  baseUrl + "/activityGift/rank/list",
    method: "get",
    params
  });
}
//鏌ョ湅娲诲姩鎺掕涓婄敤鎴锋墍鍦ㄦ埧闂翠俊鎭�
function checkUserStatus(uid,params) {
  return fetch({
    url:  baseUrl + "/activityGift/rank/checkUserStatus/"+uid,
    method: "get",
    params
  });
}
//鑾峰彇鐢ㄦ埛鍏呭€兼潯浠�
function getChargeprodConfig(params) {
  return fetch({
    url:  baseUrl + "/chargeprod/config",
    method: "get",
    params
  });
}
//鑾峰彇鐢ㄦ埛鎵€鍦ㄦ埧闂翠俊鎭�
function getUserInRoomInfo(params) {
  return fetch({
    url:  baseUrl + "/userroom/get",
    method: "get",
    params
  });
}

//娴忚鍣℉5寰俊鏀粯
function joinpayWebApply(val) {
  return fetch({
    url:  baseUrl + "/charge/joinpay/webApply",
    method: "post",
    data: qs.stringify(val),
  });
}
//娴忚鍣℉5鏀粯瀹濇敮浠�
function joinpayAliPayWebApply(val) {
  return fetch({
    url:  baseUrl + "/charge/aliPay/webApply",
    method: "post",
    data: qs.stringify(val),
  });
}
//鑾峰彇鐢ㄦ埛淇℃伅
function getShareUserInfo(params) {
  return fetch({
    url:  baseUrl + "/user/get",
    method: "get",
    params
  });
}
//鐜板湪鏀粯
function ipaynow(val) {
  return fetch({
    url:  "https://pay.ipaynow.cn/",
    method: "post",
    data: qs.stringify(val),
  });
}
//寰俊瀹樻柟鏀粯
function submitPay(val) {
  return fetch({
    url: baseUrl + "/wx/submitPay",
    method: "post",
    data: qs.stringify(val),
  });
}
//鎴块棿鏁版嵁
function queryRoomData(val) {
  return fetch({
    url: baseUrl + "/roomctrb/queryRoomData",
    method: "get",
    params: val,
  });
}
//鎴块棿鏁版嵁鎺掕姒�
function queryByType(val) {
  return fetch({
    url: baseUrl + "/roomctrb/queryByType",
    method: "get",
    params: val,
  });
}
//鍕嬬珷鏅嬪崌
function getUserMedalInfo(val) {
  return fetch({
    url: baseUrl + "/union/getUserMedalInfo",
    method: "get",
    params: val,
  });
}
//闈掑皯骞�-鑾峰彇缁戝畾鎵嬫満鍙�
function getCheckBindPhone(val) {
  return fetch({
    url: baseUrl + "/users/teens/mode/checkBindPhone",
    method: "get",
    params: val,
  });
}
//鍏細姒�
function getAllGuild(val) {
  return fetch({
    url: baseUrl + "/allrank/guild",
    method: "get",
    params: val,
  });
}
//鎯呬汉鑺�
function getValentinesRank(val) {
  return fetch({
    url: baseUrl + "/activity/getValentinesRank",
    method: "get",
    params: val,
  });
}
//鍏冨鑺�
function getLanternFestival(val) {
  return fetch({
    url: baseUrl + "/activity/getLanternFestival",
    method: "get",
    params: val,
  });
}
//鏀粯瀹濈幇鍦ㄦ敮浠�
function chargeApplyByPayNow(val) {
  return fetch({
    url: baseUrl + "/charge/applyByPayNow",
    method: "post",
    data: qs.stringify(val),
  });
}
//鏀粯瀹濇敮浠�
function chargeAlipayApply(val) {
  return fetch({
    url: baseUrl + "/charge/alipay/apply",
    method: "post",
    data: qs.stringify(val),
  });
}
//鎺屽疁浠�
function chargeZpayApply(val) {
  return fetch({
    url: baseUrl + "/charge/zpay/webApply",
    method: "post",
    data: qs.stringify(val),
  });
}
//鑾峰彇鏄ヨ妭娲诲姩淇℃伅
function springGetSpringFestival(val) {
  return fetch({
    url: baseUrl + "http://192.168.130.216:80/activity/getSpringFestival",
    method: "get",
    params: val,
  });
}

//棰嗗彇鏄ヨ妭娲诲姩澶撮グ
function springGetHeadWear(val) {
  return fetch({
    url: baseUrl + "/activity/spring/getHeadWear",
    method: "get",
    params: val,
  });
}

//棰嗗彇鏄ヨ妭娲诲姩搴ч┚
function springGetCar(val) {
  return fetch({
    url: baseUrl + "/activity/spring/getCar",
    method: "get",
    params: val,
  });
}

//鍔ㄦ€侀€佺ぜ鐗╁垪琛�
function MomentGiftList(val) {
  return fetch({
    url: baseUrl + "/user/moment/gift/list",
    method: "get",
    params: val,
  });
}
//鎶ュ悕鍞辨瓕姣旇禌
function saveSongMatch(val) {
  return fetch({
    url: baseUrl + "/activity/saveSongMatch",
    method: "post",
    data: qs.stringify(val),
  });
}
//鏌ョ湅鐢ㄦ埛姣旇禌鐘舵€�
function getUserMatchStatus(val) {
  return fetch({
    url: baseUrl + "/activity/getUserMatchStatus",
    method: "get",
    params: val,
  });
}
//鏌ョ湅姣旇禌浜烘皵姒�
function getMatchRankList(val) {
  return fetch({
    url: baseUrl + "/activity/getMatchRankList",
    method: "get",
    params: val,
  });
}
//璐拱鎴栦娇鐢ㄧ瀵嗗晢鍩庨亾鍏�
function getBuyProp(val) {
  return fetch({
    url: baseUrl + "/friend/buyProp",
    method: "post",
    data: qs.stringify(val),
  });
}
//鑾峰彇绉佸瘑鍟嗗煄淇℃伅
function getPrivateMallInfo(val) {
  return fetch({
    url: baseUrl + "/friend/getPrivateMallInfo",
    method: "post",
    data: qs.stringify(val),
  });
}
//鑾峰彇鏈濅唬娲诲姩鎺掑悕
function getDynastyList(val) {
  return fetch({
    url: baseUrl + "/activity/getDynastyList",
    method: "get",
    params: val,
  });
}
//鑾峰彇涓囧湥鑺傛椿鍔ㄦ帓鍚�
function getHalloweenReceive(val) {
  return fetch({
    url: baseUrl + "/activity/halloween/receive",
    method: "get",
    params: val,
  });
}
//鑾峰彇涓囧湥鑺傛椿鍔ㄦ帓鍚�
function getHalloweenList(val) {
  return fetch({
    url: baseUrl + "/activity/getHalloweenList",
    method: "get",
    params: val,
  });
}
//鎺ㄥ箍鏄庣粏
function extensionDetail(val) {
  return fetch({
    url: baseUrl + "/stat/extension/detail",
    method: "get",
    params: val,
  });
}
//鑾峰彇鍛ㄦ槦姒滄帓鍚�
function getStartList(val) {
  return fetch({
    url: baseUrl + "/week/star/getStartList",
    method: "get",
    params: val,
  });
}
//鑾峰彇鏈懆绀肩墿,绀肩墿棰勫憡,鍛ㄦ槦濂栧姳,涓婂懆鍛ㄦ槦绀肩墿
function getWeekStarGift(val) {
  return fetch({
    url: baseUrl + "/week/star/getWeekStarGift",
    method: "get",
    params: val,
  });
}
//鑾峰彇鏀粯娓犻亾閫夋嫨
function getConfigure(val) {
  return fetch({
    url: baseUrl + "/client/configure",
    method: "get",
    params: val,
  });
}
//鏀粯瀹濇敮浠�
function alipay(val) {
  return fetch({
    url: baseUrl + "/charge/webApply",
    method: "post",
    data: qs.stringify(val),
  });
}

//閭€璇锋帓琛�
function inviteRank(val) {
  return fetch({
    url: baseUrl + "/statpacket/inviteRank",
    method: "get",
    params: val,
  });
}
//棣栧厖棰嗗彇
function mcoinReceive(val) {
  return fetch({
    url: baseUrl + "/mcoin/activity/receive ",
    method: "post",
    data: qs.stringify(val),
  });
}
//棣栧厖鏌ヨ
function mcoinInfo(val) {
  return fetch({
    url: baseUrl + "/mcoin/activity/info",
    method: "post",
    data: qs.stringify(val),
  });
}
//鑾峰彇鐢ㄦ埛淇℃伅
function getUserInfo(val) {
  return fetch({
    url: baseUrl + "/user/v4/get",
    method: "GET",
    params: val,
  });
}
//姹囪仛鏀粯--鍏紬鍙锋敮浠�
function postTopup(val) {
  return fetch({
    url: baseUrl + "/charge/joinpay/wxPubApply",
    method: "POST",
    params: {
      chargeProdId: val.chargeProdId,
      openId: val.openId,
      payChannel: "WEIXIN_GZH",
      successUrl: Url() + "/front/topup/index.html",
      userNo: val.userNo,
      customAmount:val.customAmount
    },
  });
}
//鑾峰彇openid
function getOpenid(val) {
  return fetch({
    url: baseUrl + "/wx/snsapi/baseinfo/get",
    method: "GET",
    params: {
      code: val,
    },
  });
}
//榄呭姏绛夌骇
function getCharm(val) {
  return fetch({
    url: baseUrl + "/level/charm/get",
    method: "get",
    params: val,
  });
}
//璐㈠瘜绛夌骇
function getExeperience(val) {
  return fetch({
    url: baseUrl + "/level/exeperience/get",
    method: "get",
    params: val,
  });
}
//鑾峰彇瀹炲悕璁よ瘉淇℃伅
function getRealname(val) {
  return fetch({
    url: baseUrl + "/user/realname/v1/get",
    method: "get",
    params: val,
  });
}
//閭€璇峰鍔辨彁鐜�
function postRedpacket(val) {
  return fetch({
    url: baseUrl + "/redpacket/withdraw",
    method: "post",
    data: qs.stringify(val),
  });
}
//鍏紬鍙烽捇鐭虫彁鐜版帴鍙�
function postWithDrawCash(val) {
  return fetch({
    url: baseUrl + "/wxPublic/withDrawCash",
    method: "post",
    params: val,
  });
}
//閭€璇峰鍔卞垪琛�
function getRedpacket(val) {
  return fetch({
    url: baseUrl + "/redpacket/list",
    method: "get",
    params: val,
  });
}
//鑾峰彇鐢ㄦ埛缁戝畾閾惰鍗′俊鎭�
function getBankCardInfo(val) {
  return fetch({
    url: baseUrl + "/wxPublic/BankCardInfo",
    method: "get",
    params: val,
  });
}
//鐧诲嚭
function postLogout(val) {
  return fetch({
    url: baseUrl + "/wxPublic/logout",
    method: "post",
    data: qs.stringify(val),
  });
}
//鎺ㄨ崘浣嶈喘涔板垪琛�
function getPurseHotRoomList(val) {
  return fetch({
    url: baseUrl + "/purseHotRoom/list",
    method: "get",
    params: val,
  });
}
//鎺ㄨ崘浣嶆敮浠�
function getPurseHotRoom(val) {
  return fetch({
    url: baseUrl + "/purseHotRoom/purse",
    method: "post",
    data: qs.stringify(val),
  });
}
//鎻愮幇鑾峰彇楠岃瘉鐮佹帴鍙�
function getSmsByCode(val) {
  return fetch({
    url: baseUrl + "/wxPublic/getSmsByCode",
    method: "get",
    params: val,
  });
}
//鎵嬫満鍙风櫥褰�
function getPhoneLogin(val) {
  return fetch({
    url: baseUrl + "/wxPublic/phone/login",
    method: "get",
    params: val,
  });
}
//鏄惁灞曠ず鎻愮幇椤甸潰
function getCanShowWithdraw(val) {
  return fetch({
    url: baseUrl + "/wxPublic/canShowWithdraw",
    method: "get",
    params: val,
  });
}
//鏀粯鏂瑰紡-缁戝畾閾惰鍗�
function postBoundBankCard(val) {
  return fetch({
    url: baseUrl + "/wxPublic/boundBankCard",
    method: "post",
    data: qs.stringify(val),
  });
}
//绾㈠寘鎻愮幇璁板綍
function getPacketrecord(val) {
  return fetch({
    url: baseUrl + "/packetrecord/deposit",
    method: "get",
    params: val,
  });
}
// 鑾峰彇鎸囧畾绫诲瀷鐨勮处鍗曡褰�
function getBillrecord(val) {
  return fetch({
    url: baseUrl + "/billrecord/get",
    method: "get",
    params: val,
  });
}
//寰俊鍏紬鍙锋彁鐜板垪琛�
function getFindWithdrawal(val) {
  return fetch({
    url: baseUrl + "/wxPublic/findWithdrawal",
    method: "get",
    params: val,
  });
}
//鑾峰彇鐢ㄦ埛閽卞寘
function getUserPurse(val) {
  return fetch({
    url: baseUrl + "/purse/query",
    method: "get",
    params: val,
  });
}
//妫€鏌ョ敤鎴锋槸鍚﹀瓨鍦�
function checkUser(val) {
  return fetch({
    url: baseUrl + "/charge/checkUserH5",
    method: "get",
    params: {
      userNo: val,
    },
  });
}
//鑾峰彇鍏呭€煎垪琛�
function chargeprodList(val) {
  return fetch({
    url: baseUrl + "/chargeprod/list",
    method: "get",
    params: val,
  });
}
//鑾峰彇鎴块棿淇℃伅
function allrankGeth5(val) {
  return fetch({
    url: baseUrl + "/allrank/geth5",
    method: "get",
    params: val,
  });
}
//鑾峰彇闈掑皯骞存帴鍙ｄ俊鎭�
function getUsersTeensMode(val) {
  return fetch({
    url: baseUrl + "/users/teens/mode/getUsersTeensMode",
    method: "get",
    params: val,
  });
}
//璁剧疆闈掑皯骞存ā寮忓瘑鐮�
function teensModeSave(val) {
  return fetch({
    url: baseUrl + "/users/teens/mode/save",
    method: "post",
    params: val,
  });
}
//鍏抽棴闈掑皯骞存ā寮忓瘑鐮�
function closeTeensMode(val) {
  return fetch({
    url: baseUrl + "/users/teens/mode/closeTeensMode",
    method: "post",
    data: qs.stringify(val),
  });
}
//鏍￠獙闈掑皯骞存ā寮忓瘑鐮�
function checkCipherCode(val) {
  return fetch({
    url: baseUrl + "/users/teens/mode/checkCipherCode",
    method: "get",
    params: val,
  });
}
//閭€璇峰ソ鍙�
function invitedetail(val) {
  return fetch({
    url: baseUrl + "/statpacket/invitedetail",
    method: "get",
    params: val,
  });
}
//鏇存柊閭€璇风爜
function updateCode(val) {
  return fetch({
    url: baseUrl + "/user/update",
    method: "post",
    data: qs.stringify(val),
  });
}
//
//鍏戞崲閲戝竵
function exchangeGold(val) {
  return fetch({
    url: baseUrl + "/wxPublic/exchangeGold",
    method: "post",
    data: qs.stringify(val),
  });
}
// 鑾峰彇鎶藉鍒楄〃
function getGiftList(val) {
  return fetch({
    url: baseUrl + "/draw/getGiftList",
    method: "get",
    params: val,
  });
}
// 鑾峰彇鎶藉娆℃暟
function getDrawUserInfo(val) {
  return fetch({
    url: baseUrl + "/draw/getUserInfo",
    method: "get",
    params: val,
  });
}
// 鎶藉璁板綍
function getGiftRecordList(val) {
  return fetch({
    url: baseUrl + "/draw/list",
    method: "get",
    params: val,
  });
}
//鍏呭€兼娊濂栨娊濂�
function doDraw(val) {
  return fetch({
    url: baseUrl + "/draw/doDraw",
    method: "post",
    params: val,
  });
}
function getOderList(val,phone){
  return fetch({
    url: baseUrl + "/open/listOrder/"+phone,
    method: "get",
    params: val,
  });
}
//妫€鏌ユ墜鏈虹被鍨�
function checkVersion() {
  var u = navigator.userAgent,
    app = navigator.appVersion;
  return {
    trident: u.indexOf("Trident") > -1, //IE鍐呮牳
    presto: u.indexOf("Presto") > -1, //opera鍐呮牳
    webKit: u.indexOf("AppleWebKit") > -1, //鑻规灉銆佽胺姝屽唴鏍�
    gecko: u.indexOf("Gecko") > -1 && u.indexOf("KHTML") == -1, //鐏嫄鍐呮牳
    mobile: !!u.match(/AppleWebKit.*Mobile.*/), //鏄惁涓虹Щ鍔ㄧ粓绔�
    ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios缁堢
    android: u.indexOf("Android") > -1 || u.indexOf("Adr") > -1, //android缁堢
    iPhone: u.indexOf("iPhone") > -1, //鏄惁涓篿Phone鎴栬€匭QHD娴忚鍣�
    iPad: u.indexOf("iPad") > -1, //鏄惁iPad
    webApp: u.indexOf("Safari") == -1, //鏄惁web搴旇绋嬪簭锛屾病鏈夊ご閮ㄤ笌搴曢儴
    weixin: u.indexOf("MicroMessenger") > -1, //鏄惁寰俊
    qq: u.match(/\sQQ/i) == " qq", //鏄惁QQ
    app: u.indexOf("miaomiaoApp") > -1, //鏄惁鍦╝pp鍐�
  };
}