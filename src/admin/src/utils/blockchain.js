    // Step 1 ==================================
    var async = require('async');
    var atob = require('atob');
    var Ibc1 = require('ibm-blockchain-js');
    var ibc = new Ibc1(/*logger*/);             //you can pass a logger such as winston here - optional
    var chaincode = {};

var record = new Array;
    // ==================================
    // configure ibc-js sdk
    // ==================================
    var options =   {
        network:{
            peers:   [{
                "api_host": "b22009c6959f42a08e56f7b743ba34c2-vp2.us.blockchain.ibm.com",
                "api_port": 5004,
                "api_port_tls": 5004,
                "id": "b22009c6959f42a08e56f7b743ba34c2-vp2"
            }],
            users:  [{
                "enrollId": "admin",
                "enrollSecret": "cab52bfc67",
                "username": "admin",
		        "secret": "cab52bfc67" 
            }],
            options: {                          //this is optional
                quiet: true, 
                timeout: 60000
            }
        },
        
        chaincode:{
            zip_url: 'https://github.com/ibm-blockchain/marbles-chaincode/archive/master.zip',
		    unzip_dir: 'marbles-chaincode-master/part2',
		    git_url: 'https://github.com/ibm-blockchain/marbles-chaincode/part2'
        }
    };

    // Load the Marbles2 chaincode, with defined options, and return call-back-when-ready function.
//ibc.load(options, cb_ready);

// Define the call-back-when-ready function returned above
// call-back-when-ready function has err
function cb_ready(err, cc){
	//response has chaincode functions
	
	// if the deployed name is blank, then chaincode has not been deployed
	if(cc.details.deployed_name === ""){
        cc.deploy('init', ['99'], './cc_summaries', cb_deployed);
        function cb_deployed(err){
			console.log('sdk has deployed code and waited');
  		} 
  	}
  	else{
  		console.log('chaincode summary file indicates chaincode has been previously deployed');
	}
};

//ibc.switchPeer(1);

/*
ibc.chain_stats(stats_callback);
function stats_callback(e, stats){ 
	console.log('got some stats', stats);
}
*/

//ibc.chain_stats(cb_chainstats);

//call back for getting the blockchain stats, lets get the block stats now
	function cb_chainstats(e, chain_stats){
		if(chain_stats && chain_stats.height){
			chain_stats.height = chain_stats.height - 1;								//its 1 higher than actual height
			var list = [];
			for(var i = chain_stats.height; i >= 1; i--){								//create a list of heights we need
				list.push(i);
				if(list.length >= 10) break;
			}
			console.log("list.length" + list.length);
			//list.reverse();		
			record.splice(0,record.length);													//flip it so order is correct in UI
			async.eachLimit(list, 1, function(block_height, cb) {						//iter through each one, and send it
				ibc.block_stats(block_height, function(e, stats){
					if(e == null){
						stats.height = block_height;
						//sendMsg({msg: 'chainstats', e: e, chainstats: chain_stats, blockstats: stats});
                        if(stats.transactions && stats.transactions[0].type=='2'){
                            console.log(stats);
                            var ccid = formatCCID(stats.transactions[0].type, stats.transactions[0].uuid, atob(stats.transactions[0].chaincodeID));
	                        var payload = atob(stats.transactions[0].payload);
                            //console.log("get the payload:" + formatPayload(payload, ccid));
							var userstr = formatPayload(payload, ccid);
							console.log("get the payload:" + userstr);
							var str = userstr.split("\n");
							console.log(str[1] + " " + str[2]  + " " + str[3] + " "  + str[4]);
							if(str.length==5){
								var user= { id: 1, name: 'myname', address: 'myaddress', age: 'myage', date: 'mydate', tdate: '111111', txid: ''};
							user.name=str[3];
							user.address = str[4];
							user.age=str[1];
							user.date=str[2];
							var tdate = new Date(stats.transactions[0].timestamp.seconds*1000);
							//user.tdate=tdate.toUTCString().replace(/T/, ' ').replace(/\..+/, ''); 
							user.tdate=tdate.toLocaleString(); 
							user.txid= stats.transactions[0].txid;
							record.push(user);
							}
							
							
                        }
					}
					cb(null);
				});
			}, function() {
			});
		}
	}

function formatCCID(i, uuid, ccid){								//flip uuid and ccid if deploy, weird i know
	if(i == 1) return uuid;
	return ccid;
}

function formatUUID(i, uuid){									//blank uuid if deploy, its just ccid again
	if(i == 1) return '-';
	return uuid;
}

function formatType(i){											//spell out deploy or invoke
	if(i == 1) return 'deploy';
	if(i == 3) return 'invoke';
	return i;
}

function formatPayload(str, ccid){								//create a sllliiiggghhhtttlllllyyy better payload name from decoded payload
	var func = ['init', 'delete', 'write', 'init_marble', 'set_user', 'open_trade', 'perform_trade', 'remove_trade'];
	str =  str.substring(str.indexOf(ccid) + ccid.length + 4);
	for(var i in func){
		if(str.indexOf(func[i]) >= 0){
			return func[i] + ': ' + str.substr(func[i].length);
		}
	}
	return str;
}    


exports.loadBlockChainData = function(){
    ibc.load(options, cb_ready);
	ibc.chain_stats(cb_chainstats);
	return record;
}