package main

//WARNING - this chaincode's ID is hard-coded in chaincode_example04 to illustrate one way of
//calling chaincode from a chaincode. If this example is modified, chaincode_example04.go has
//to be modified as well with the new ID of chaincode_example02.
//chaincode_example05 show's how chaincode ID can be passed in as a parameter instead of
//hard-coding.

import (
        "errors"
        "fmt"
        "strconv"

        "encoding/json"

        "github.com/hyperledger/fabric/core/chaincode/shim"
)

// SimpleChaincode example simple Chaincode implementation
type SimpleChaincode struct {
}

func (t *SimpleChaincode) Init(stub shim.ChaincodeStubInterface, function string, args []string) ([]byte, error) {
        var username, shopA, shopB string    // Entities
        var err error

        if len(args) != 3 {
                return nil, errors.New("Incorrect number of arguments. Expecting 3: username, shopA, shopB")
        }

        // Initialize the chaincode
        username = args[0]
        shopA = args[1]
        shopB = args[2]

        fmt.Printf("username = %s, shopA= %s, shopB = %s\n", username, shopA, shopB)

        var user_A, user_B, user_A_B, user_B_A string
        user_A = username + "_" + shopA
        user_B = username + "_" + shopB
        user_A_B = username + "_" + shopA + "_" + shopB
        user_B_A = username + "_" + shopB + "_" + shopA

        // Write the state to the ledger
        err = stub.PutState("user", []byte(username))
        if err != nil {
                return nil, err
        }

        shops := []string{shopA, shopB}
        shopsBytes, _ := json.Marshal(shops)

        err = stub.PutState(username, shopsBytes)
        if err != nil {
                return nil, err
        }

        err = stub.PutState(user_A, []byte(strconv.Itoa(0)))
        if err != nil {
                return nil, err
        }
        err = stub.PutState(user_B, []byte(strconv.Itoa(0)))
        if err != nil {
                return nil, err
        }
        err = stub.PutState(user_A_B, []byte(strconv.Itoa(0)))
        if err != nil {
                return nil, err
        }

        err = stub.PutState(user_B_A, []byte(strconv.Itoa(0)))
        if err != nil {
                return nil, err
        }

        return nil, nil
}

// Transaction
// 1. add user, shop, points:  add, username, shopName, xx points
// 2. user1 spent shopA's points in shopB by xx points:  consume, username, shopA, shopB, xx points
func (t *SimpleChaincode) Invoke(stub shim.ChaincodeStubInterface, function string, args []string) ([]byte, error) {

        fmt.Println("Invoke running. Function: " + function)

        if function == "add" {
                return t.add(stub, args)
        } else if function == "consume" || function == "spend" {
                return t.spend(stub, args)
        }

        return nil, errors.New("Received unknown function invocation: " + function)


}

func (t *SimpleChaincode) add(stub shim.ChaincodeStubInterface, args []string) ([]byte, error) {
        var username, shopname, user_shop string
        var points, cur_points int  // accumulated points
        var err error

        if len(args) != 3 {
                return nil, errors.New("Incorrect number of arguments. Expecting 3: username, shopname, points")
        }
        username = args[0]
        shopname = args[1]
        points, _ = strconv.Atoi(args[2])

        fmt.Println("add: got param: " + username + "," + shopname + "," + args[2])

        user_shop = username + "_" + shopname

        pointsBytes, err := stub.GetState(user_shop)

        if err != nil {
                return nil, errors.New("Failed to get state: " + user_shop)
        }
        if pointsBytes == nil {
                return nil, errors.New("Entity not found: " + user_shop)
        }
        cur_points, _ = strconv.Atoi(string(pointsBytes))

        cur_points = cur_points + points
        fmt.Printf("After add points:%d, cur_points:%d\n", points, cur_points)

        // Write the state back to the ledger
        err = stub.PutState(user_shop, []byte(strconv.Itoa(cur_points)))
        if err != nil {
                return nil, err
        }

        return nil, nil

}

func (t *SimpleChaincode) spend(stub shim.ChaincodeStubInterface, args []string) ([]byte, error) {
        var username, spend_shopname, spent_shopname, user_spent_shop, user_spend_spent_shop string
        var points, cur_points int  // accumulated points
        var err error

        if len(args) != 4 {
                return nil, errors.New("Incorrect number of arguments. Expecting 4: username, spend_shop, spent_shop, points")
        }
        username = args[0]
        spend_shopname = args[1]
        spent_shopname = args[2]
        points, err = strconv.Atoi(args[3])
        fmt.Println("spend: got param: " + username + "," + spend_shopname + "," + spent_shopname + ", " + args[3])

        //user_spend_shop = username + "_" + spend_shopname
        user_spent_shop = username + "_" + spent_shopname
        user_spend_spent_shop = username + "_" + spend_shopname + "_" + spent_shopname

        // Subtract spent shop's points and record user_spend_spent_shop value. This is the points that is how many points shopA spend shopB
        var user_shop string
        user_shop = user_spent_shop
        pointsBytes, err := stub.GetState(user_shop)

        if err != nil {
                return nil, errors.New("Failed to get state: " + user_shop)
        }
        if pointsBytes == nil {
                return nil, errors.New("Entity not found: " + user_shop)
        }
        cur_points, _ = strconv.Atoi(string(pointsBytes))

        cur_points = cur_points - points
        fmt.Printf("After spend points, user-shop's ledger:%s, points: %d, cur_points:%d\n", user_shop, points, cur_points)

        // Write the state back to the ledger
        err = stub.PutState(user_shop, []byte(strconv.Itoa(cur_points)))
        if err != nil {
                return nil, err
        }

        user_shop = user_spend_spent_shop
        pointsBytes, err = stub.GetState(user_shop)

        if err != nil {
                return nil, errors.New("Failed to get state: " + user_shop)
        }
        if pointsBytes == nil {
                return nil, errors.New("Entity not found: " + user_shop)
        }
        cur_points, _ = strconv.Atoi(string(pointsBytes))

        cur_points = cur_points + points
        fmt.Printf("After spend points, user-shops' ledger:%s, points:%d, cur_points %s\n", user_shop, points, cur_points)

        // Write the state back to the ledger
        err = stub.PutState(user_shop, []byte(strconv.Itoa(cur_points)))
        if err != nil {
                return nil, err
        }


        return nil, nil

}


// Query callback representing the query of a chaincode
func (t *SimpleChaincode) Query(stub shim.ChaincodeStubInterface, function string, args []string) ([]byte, error) {
        fmt.Println("Query running. Function: " + function)

        if function == "query_user" || function == "query" {
                return t.query_user(stub, args)
        } else if function == "query_shop" {
                return t.query_shop(stub, args)
        }
	return []byte("No such function"), nil
}

func (t *SimpleChaincode) query_user(stub shim.ChaincodeStubInterface, args []string) ([]byte, error) {
        var username, shopA, shopB, user_A, user_B string
        var shops []string
        var shopA_points, shopB_points int  // accumulated points
        var err error

        if len(args) != 1 {
                return nil, errors.New("Incorrect number of arguments. Expecting 1: username")
        }
        username = args[0]
        fmt.Println("query_user: got param: " + username )


        shopsBytes, err := stub.GetState(username)
        err = json.Unmarshal(shopsBytes, &shops)
        if err != nil {
            fmt.Println("Error unmarshalling user's shops: " + username + "\n--->: " + err.Error())
            return nil, errors.New("Error unmarshalling user's shops " + username)
        }
        shopA = shops[0]
        shopB = shops[1]

        user_A = username + "_" + shopA
        user_B = username + "_" + shopB

        user_shop := user_A
        pointsBytes, err := stub.GetState(user_shop)

        if err != nil {
                return nil, errors.New("Failed to get state: " + user_shop)
        }
        if pointsBytes == nil {
                return nil, errors.New("Entity not found: " + user_shop)
        }
        shopA_points, _ = strconv.Atoi(string(pointsBytes))


        user_shop = user_B
        pointsBytes, err = stub.GetState(user_shop)

        if err != nil {
                return nil, errors.New("Failed to get state: " + user_shop)
        }
        if pointsBytes == nil {
                return nil, errors.New("Entity not found: " + user_shop)
        }
        shopB_points, _ = strconv.Atoi(string(pointsBytes))

        resp:= map[string]int{
           shopA: shopA_points,
           shopB:   shopB_points,
        }

        jsonResp, err := json.Marshal(resp)
         if err != nil {
                return nil, errors.New("resp marshal fail" )
        }
        fmt.Printf("Query Response:%s\n", jsonResp)
        return []byte(jsonResp), nil
}

// 
// Settle shops' points
//
func (t *SimpleChaincode) query_shop(stub shim.ChaincodeStubInterface, args []string) ([]byte, error) {
        var username, shopA, shopB, user_A, user_B string
        var shops []string
        var shopA_points, shopB_points int  // accumulated points
        var err error

        if len(args) != 1 {
                return nil, errors.New("Incorrect number of arguments 2. Expecting: shopA, shopB")
        }
        username = "phyllis"
        shopA = args[0]
        shopB = args[1]
        fmt.Println("query_shop: got param: " + shopA + "," + shopB)

        //var user_A_B, user_B_A string
        user_A = username + "_" + shopA
        user_B = username + "_" + shopB
        //user_A_B = username + "_" + shopA + "_" + shopB
        //user_B_A = username + "_" + shopB + "_" + shopA


        shopsBytes, err := stub.GetState(username)
        err = json.Unmarshal(shopsBytes, &shops)
        if err != nil {
            fmt.Println("Error unmarshalling user's shops: " + username + "\n--->: " + err.Error())
            return nil, errors.New("Error unmarshalling user's shops " + username)
        }
        shopA = shops[0]
        shopB = shops[1]

        user_A = username + "_" + shopA
        user_B = username + "_" + shopB

        user_shop := user_A
        pointsBytes, err := stub.GetState(user_shop)

        if err != nil {
                return nil, errors.New("Failed to get state: " + user_shop)
        }
        if pointsBytes == nil {
                return nil, errors.New("Entity not found: " + user_shop)
        }
        shopA_points, _ = strconv.Atoi(string(pointsBytes))


        user_shop = user_B
        pointsBytes, err = stub.GetState(user_shop)

        if err != nil {
                return nil, errors.New("Failed to get state: " + user_shop)
        }
        if pointsBytes == nil {
                return nil, errors.New("Entity not found: " + user_shop)
        }
        shopB_points, _ = strconv.Atoi(string(pointsBytes))

        resp:= map[string]int{
           shopA: shopA_points,
           shopB:   shopB_points,
        }

        jsonResp, err := json.Marshal(resp)
         if err != nil {
                return nil, errors.New("resp marshal fail" )
        }
        fmt.Printf("Query Response:%s\n", jsonResp)
        return []byte(jsonResp), nil

}
func main() {
        err := shim.Start(new(SimpleChaincode))
        if err != nil {
                fmt.Printf("Error starting Simple chaincode: %s", err)
        }
}

