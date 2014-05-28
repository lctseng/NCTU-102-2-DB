<?php
    $transfer_sql_all = array();
    # ? 的順序：出發地、目標地、出發地、目標地、出發地、目標地
    $transfer_sql_all[2] = <<<DOC_SQL
SELECT id1,id2,id3,f_time1,f_time2,f_time3,ADDTIME(f_time1,ADDTIME(f_time2,f_time3))  AS flight_time,transfer_time,(ADDTIME(transfer_time,ADDTIME(f_time1,ADDTIME(f_time2,f_time3)))) AS total_time,total_price FROM  
( 
    (SELECT id AS id1,null AS id2,null AS id3,
        (
            TIMEDIFF( -- 直航
                -- 抵達時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(arrival_date , MAKETIME(arrive_hour,arrive_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.destination -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
                ,
                -- 出發時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(departure_date , MAKETIME(depart_hour,depart_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.departure -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
            )
        )
        AS f_time1,
        '00:00:00' AS f_time2,
        '00:00:00' AS f_time3,
        (
            ADDTIME(Flight.departure_date , MAKETIME(Flight.depart_hour,Flight.depart_min,0))
        )
        AS depart_time,
        (
            ADDTIME(Flight.arrival_date , MAKETIME(Flight.arrive_hour,Flight.arrive_min,0))
        )
        AS arrive_time,
        '00:00:00' AS transfer_time,
        
        
        ( -- 總票價
            price
        )
        AS total_price 
        FROM Flight
        WHERE departure = ? AND destination = ?
    ) 
    UNION
    (SELECT id1,id2,id3,f_time1,f_time2,f_time3,depart_time,arrive_time,time_1 AS transfer_time,total_price -- 轉機一次
        
        FROM (
            SELECT Flight1.id AS id1,Flight2.id AS id2,null AS id3,
            (
                TIMEDIFF( -- 班機1
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time1,
            (
                TIMEDIFF( -- 班機2
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time2,
            '00:00:00' AS f_time3,
            (
                ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0))
                
            )
            AS depart_time,
            (
                ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0))
            )
            AS arrive_time,
            ( -- 總票價
                ROUND((Flight1.price + Flight2.price)*0.9) -- 打九折之後四捨五入
            )
            AS total_price,
            ( -- 轉機時間1~2
                
                TIMEDIFF(
                    -- 班機二出發時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                ,
                    -- 班機一抵達時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS time_1,
            null
            AS time_2
            FROM Flight AS Flight1 JOIN Flight AS Flight2 
            WHERE Flight1.departure = ?  -- 出發地點
            AND Flight1.destination = Flight2.departure -- 1&2間中繼站必須相等
            AND Flight2.destination = ?  -- 抵達地點
        ) AS MatchFlight
        WHERE MatchFlight.time_1 >= '0000-00-00 02:00:00' -- 檢查轉機時間是否足夠
    )
    UNION
    (SELECT id1,id2,id3,f_time1,f_time2,f_time3,depart_time,arrive_time, ADDTIME(time_1,time_2) AS transfer_time,total_price -- 轉機兩次
        
        FROM (
            SELECT Flight1.id AS id1,Flight2.id AS id2,Flight3.id AS id3,
            (
                TIMEDIFF( -- 班機1
                        -- 抵達時間(UTC)
                        SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                            ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                            )
                            , 
                            SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                                (
                                    SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                                )
                                ,'12:00:00' -- 逆位移12小時
                            )
                        )
                        ,
                        -- 出發時間(UTC)
                        SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                            ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0)  -- 組合真正的出發日期時間
                            )
                            , 
                            SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                                (
                                    SELECT time_zone FROM Airport WHERE Airport.name = Flight1.departure -- 取的位移後的時區
                                )
                                ,'12:00:00' -- 逆位移12小時
                            )
                        )
                    )
            )
            AS f_time1,
            (
                TIMEDIFF( -- 班機2
                        -- 抵達時間(UTC)
                        SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                            ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0)  -- 組合真正的出發日期時間
                            )
                            , 
                            SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                                (
                                    SELECT time_zone FROM Airport WHERE Airport.name = Flight2.destination -- 取的位移後的時區
                                )
                                ,'12:00:00' -- 逆位移12小時
                            )
                        )
                        ,
                        -- 出發時間(UTC)
                        SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                            ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                            )
                            , 
                            SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                                (
                                    SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                                )
                                ,'12:00:00' -- 逆位移12小時
                            )
                        )
                    )
            )
            AS f_time2,
            (
                TIMEDIFF( -- 班機3
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight3.arrival_date , MAKETIME(Flight3.arrive_hour,Flight3.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight3.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight3.departure_date , MAKETIME(Flight3.depart_hour,Flight3.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight3.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time3,
            ( -- 轉機時間1~2
                
                TIMEDIFF(
                    -- 班機二出發時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                ,
                    -- 班機一抵達時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS time_1,
            ( -- 轉機時間2~3
                
                TIMEDIFF(
                    -- 班機三出發時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight3.departure_date , MAKETIME(Flight3.depart_hour,Flight3.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight3.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                ,
                    -- 班機二抵達時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS time_2,
            (
                ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0))
                
            )
            AS depart_time,
            (
                ADDTIME(Flight3.arrival_date , MAKETIME(Flight3.arrive_hour,Flight3.arrive_min,0))
            )
            AS arrive_time,
            ( -- 總票價
                ROUND((Flight1.price + Flight2.price + Flight3.price)*0.8) -- 打九折之後四捨五入
            )
            AS total_price
            FROM Flight AS Flight1 JOIN Flight AS Flight2 JOIN Flight AS Flight3
            WHERE Flight1.departure = ?  -- 出發地點
            AND Flight1.destination = Flight2.departure -- 1&2間中繼站必須相等
            AND Flight2.destination <> Flight1.departure -- 迴圈
            AND Flight2.destination = Flight3.departure -- 2&3間中繼站必須相等
            AND Flight3.destination = ?  -- 抵達地點
        ) AS MatchFlight
        WHERE MatchFlight.time_1 > '0000-00-00 02:00:00' -- 檢查1&2轉機時間是否足夠
        AND MatchFlight.time_2 > '0000-00-00 02:00:00' -- 檢查2&3轉機時間是否足夠
    )
) 
AS TransferTable
ORDER BY 
DOC_SQL;

    $transfer_sql_all[1] = <<<DOC_SQL
SELECT id1,id2,id3,f_time1,f_time2,f_time3,ADDTIME(f_time1,ADDTIME(f_time2,f_time3))  AS flight_time,transfer_time,(ADDTIME(transfer_time,ADDTIME(f_time1,ADDTIME(f_time2,f_time3)))) AS total_time,total_price,depart_time,arrive_time FROM 
( 
    (SELECT id AS id1,null AS id2,null AS id3,
        (
            TIMEDIFF( -- 直航
                -- 抵達時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(arrival_date , MAKETIME(arrive_hour,arrive_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.destination -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
                ,
                -- 出發時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(departure_date , MAKETIME(depart_hour,depart_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.departure -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
            )
        )
        AS f_time1,
        '00:00:00' AS f_time2,
        '00:00:00' AS f_time3,
        (
            ADDTIME(Flight.departure_date , MAKETIME(Flight.depart_hour,Flight.depart_min,0))
        )
        AS depart_time,
        (
            ADDTIME(Flight.arrival_date , MAKETIME(Flight.arrive_hour,Flight.arrive_min,0))
        )
        AS arrive_time,
        '00:00:00' AS transfer_time,
        
        
        ( -- 總票價
            price
        )
        AS total_price 
        FROM Flight
        WHERE departure = ? AND destination = ?
    ) 
    UNION
    (SELECT id1,id2,id3,f_time1,f_time2,f_time3,depart_time,arrive_time,time_1 AS transfer_time,total_price -- 轉機一次
        
        FROM (
            SELECT Flight1.id AS id1,Flight2.id AS id2,null AS id3,
            (
                TIMEDIFF( -- 班機1
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time1,
            (
                TIMEDIFF( -- 班機2
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time2,
            '00:00:00' AS f_time3,
            (
                ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0))
                
            )
            AS depart_time,
            (
                ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0))
            )
            AS arrive_time,
            ( -- 總票價
                ROUND((Flight1.price + Flight2.price)*0.9) -- 打九折之後四捨五入
            )
            AS total_price,
            ( -- 轉機時間1~2
                
                TIMEDIFF(
                    -- 班機二出發時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                ,
                    -- 班機一抵達時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS time_1,
            null
            AS time_2
            FROM Flight AS Flight1 JOIN Flight AS Flight2 
            WHERE Flight1.departure = ?  -- 出發地點
            AND Flight1.destination = Flight2.departure -- 1&2間中繼站必須相等
            AND Flight2.destination = ?  -- 抵達地點
        ) AS MatchFlight
        WHERE MatchFlight.time_1 >= '0000-00-00 02:00:00' -- 檢查轉機時間是否足夠
    )
) 
AS TransferTable
ORDER BY 
DOC_SQL;

    $transfer_sql_all[0] = <<<DOC_SQL
SELECT id1,id2,id3,f_time1,f_time2,f_time3,ADDTIME(f_time1,ADDTIME(f_time2,f_time3))  AS flight_time,transfer_time,(ADDTIME(transfer_time,ADDTIME(f_time1,ADDTIME(f_time2,f_time3)))) AS total_time,total_price,depart_time,arrive_time FROM 
( 
    (SELECT id AS id1,null AS id2,null AS id3,
        (
            TIMEDIFF( -- 直航
                -- 抵達時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(arrival_date , MAKETIME(arrive_hour,arrive_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.destination -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
                ,
                -- 出發時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(departure_date , MAKETIME(depart_hour,depart_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.departure -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
            )
        )
        AS f_time1,
        '00:00:00' AS f_time2,
        '00:00:00' AS f_time3,
        (
            ADDTIME(Flight.departure_date , MAKETIME(Flight.depart_hour,Flight.depart_min,0))
        )
        AS depart_time,
        (
            ADDTIME(Flight.arrival_date , MAKETIME(Flight.arrive_hour,Flight.arrive_min,0))
        )
        AS arrive_time,
        '00:00:00' AS transfer_time,
        
        
        ( -- 總票價
            price
        )
        AS total_price 
        FROM Flight
        WHERE departure = ? AND destination = ?
    ) 
    
) 
AS TransferTable
ORDER BY 
DOC_SQL;
    $transfer_sql_night = array();
    # ? 的順序：出發地、目標地、出發地、目標地、出發地、目標地
    $transfer_sql_night[2] = <<<DOC_SQL
SELECT id1,id2,id3,f_time1,f_time2,f_time3,ADDTIME(f_time1,ADDTIME(f_time2,f_time3))  AS flight_time,transfer_time,(ADDTIME(transfer_time,ADDTIME(f_time1,ADDTIME(f_time2,f_time3)))) AS total_time,total_price FROM  
( 
    (SELECT id AS id1,null AS id2,null AS id3,
        (
            TIMEDIFF( -- 直航
                -- 抵達時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(arrival_date , MAKETIME(arrive_hour,arrive_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.destination -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
                ,
                -- 出發時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(departure_date , MAKETIME(depart_hour,depart_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.departure -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
            )
        )
        AS f_time1,
        '00:00:00' AS f_time2,
        '00:00:00' AS f_time3,
        (
            ADDTIME(Flight.departure_date , MAKETIME(Flight.depart_hour,Flight.depart_min,0))
        )
        AS depart_time,
        (
            ADDTIME(Flight.arrival_date , MAKETIME(Flight.arrive_hour,Flight.arrive_min,0))
        )
        AS arrive_time,
        '00:00:00' AS transfer_time,
        
        
        ( -- 總票價
            price
        )
        AS total_price 
        FROM Flight
        WHERE departure = ? AND destination = ?
    ) 
    UNION
    (SELECT id1,id2,id3,f_time1,f_time2,f_time3,depart_time,arrive_time,time_1 AS transfer_time,total_price -- 轉機一次
        
        FROM (
            SELECT Flight1.id AS id1,Flight2.id AS id2,null AS id3,
            (
                TIMEDIFF( -- 班機1
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time1,
            (
                TIMEDIFF( -- 班機2
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time2,
            '00:00:00' AS f_time3,
            (
                ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0))
                
            )
            AS depart_time,
            (
                ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0))
            )
            AS arrive_time,
            ( -- 總票價
                ROUND((Flight1.price + Flight2.price)*0.9) -- 打九折之後四捨五入
            )
            AS total_price,
            ( -- 轉機時間1~2
                
                TIMEDIFF(
                    -- 班機二出發時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                ,
                    -- 班機一抵達時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS time_1,
            null
            AS time_2
            FROM Flight AS Flight1 JOIN Flight AS Flight2 
            WHERE Flight1.departure = ?  -- 出發地點
            AND Flight1.destination = Flight2.departure -- 1&2間中繼站必須相等
            AND Flight2.destination = ?  -- 抵達地點
        ) AS MatchFlight
        WHERE MatchFlight.time_1 >= '0000-00-00 02:00:00' -- 檢查轉機時間是否足夠
        AND MatchFlight.time_1 <= '0000-00-00 12:00:00'
    )
    UNION
    (SELECT id1,id2,id3,f_time1,f_time2,f_time3,depart_time,arrive_time, ADDTIME(time_1,time_2) AS transfer_time,total_price -- 轉機兩次
        
        FROM (
            SELECT Flight1.id AS id1,Flight2.id AS id2,Flight3.id AS id3,
            (
                TIMEDIFF( -- 班機1
                        -- 抵達時間(UTC)
                        SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                            ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                            )
                            , 
                            SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                                (
                                    SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                                )
                                ,'12:00:00' -- 逆位移12小時
                            )
                        )
                        ,
                        -- 出發時間(UTC)
                        SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                            ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0)  -- 組合真正的出發日期時間
                            )
                            , 
                            SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                                (
                                    SELECT time_zone FROM Airport WHERE Airport.name = Flight1.departure -- 取的位移後的時區
                                )
                                ,'12:00:00' -- 逆位移12小時
                            )
                        )
                    )
            )
            AS f_time1,
            (
                TIMEDIFF( -- 班機2
                        -- 抵達時間(UTC)
                        SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                            ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0)  -- 組合真正的出發日期時間
                            )
                            , 
                            SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                                (
                                    SELECT time_zone FROM Airport WHERE Airport.name = Flight2.destination -- 取的位移後的時區
                                )
                                ,'12:00:00' -- 逆位移12小時
                            )
                        )
                        ,
                        -- 出發時間(UTC)
                        SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                            ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                            )
                            , 
                            SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                                (
                                    SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                                )
                                ,'12:00:00' -- 逆位移12小時
                            )
                        )
                    )
            )
            AS f_time2,
            (
                TIMEDIFF( -- 班機3
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight3.arrival_date , MAKETIME(Flight3.arrive_hour,Flight3.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight3.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight3.departure_date , MAKETIME(Flight3.depart_hour,Flight3.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight3.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time3,
            ( -- 轉機時間1~2
                
                TIMEDIFF(
                    -- 班機二出發時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                ,
                    -- 班機一抵達時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS time_1,
            ( -- 轉機時間2~3
                
                TIMEDIFF(
                    -- 班機三出發時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight3.departure_date , MAKETIME(Flight3.depart_hour,Flight3.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight3.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                ,
                    -- 班機二抵達時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS time_2,
            (
                ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0))
                
            )
            AS depart_time,
            (
                ADDTIME(Flight3.arrival_date , MAKETIME(Flight3.arrive_hour,Flight3.arrive_min,0))
            )
            AS arrive_time,
            ( -- 總票價
                ROUND((Flight1.price + Flight2.price + Flight3.price)*0.8) -- 打九折之後四捨五入
            )
            AS total_price
            FROM Flight AS Flight1 JOIN Flight AS Flight2 JOIN Flight AS Flight3
            WHERE Flight1.departure = ?  -- 出發地點
            AND Flight1.destination = Flight2.departure -- 1&2間中繼站必須相等
            AND Flight2.destination <> Flight1.departure -- 迴圈
            AND Flight2.destination = Flight3.departure -- 2&3間中繼站必須相等
            AND Flight3.destination = ?  -- 抵達地點
        ) AS MatchFlight
        WHERE MatchFlight.time_1 > '0000-00-00 02:00:00' -- 檢查1&2轉機時間是否足夠
        AND MatchFlight.time_1 <= '0000-00-00 12:00:00' 
        AND MatchFlight.time_2 > '0000-00-00 02:00:00' -- 檢查2&3轉機時間是否足夠
        AND MatchFlight.time_2 <= '0000-00-00 12:00:00' 
    )
) 
AS TransferTable
ORDER BY 
DOC_SQL;

    $transfer_sql_night[1] = <<<DOC_SQL
SELECT id1,id2,id3,f_time1,f_time2,f_time3,ADDTIME(f_time1,ADDTIME(f_time2,f_time3))  AS flight_time,transfer_time,(ADDTIME(transfer_time,ADDTIME(f_time1,ADDTIME(f_time2,f_time3)))) AS total_time,total_price,depart_time,arrive_time FROM 
( 
    (SELECT id AS id1,null AS id2,null AS id3,
        (
            TIMEDIFF( -- 直航
                -- 抵達時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(arrival_date , MAKETIME(arrive_hour,arrive_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.destination -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
                ,
                -- 出發時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(departure_date , MAKETIME(depart_hour,depart_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.departure -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
            )
        )
        AS f_time1,
        '00:00:00' AS f_time2,
        '00:00:00' AS f_time3,
        (
            ADDTIME(Flight.departure_date , MAKETIME(Flight.depart_hour,Flight.depart_min,0))
        )
        AS depart_time,
        (
            ADDTIME(Flight.arrival_date , MAKETIME(Flight.arrive_hour,Flight.arrive_min,0))
        )
        AS arrive_time,
        '00:00:00' AS transfer_time,
        
        
        ( -- 總票價
            price
        )
        AS total_price 
        FROM Flight
        WHERE departure = ? AND destination = ?
    ) 
    UNION
    (SELECT id1,id2,id3,f_time1,f_time2,f_time3,depart_time,arrive_time,time_1 AS transfer_time,total_price -- 轉機一次
        
        FROM (
            SELECT Flight1.id AS id1,Flight2.id AS id2,null AS id3,
            (
                TIMEDIFF( -- 班機1
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time1,
            (
                TIMEDIFF( -- 班機2
                    -- 抵達時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                    ,
                    -- 出發時間(UTC)
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS f_time2,
            '00:00:00' AS f_time3,
            (
                ADDTIME(Flight1.departure_date , MAKETIME(Flight1.depart_hour,Flight1.depart_min,0))
                
            )
            AS depart_time,
            (
                ADDTIME(Flight2.arrival_date , MAKETIME(Flight2.arrive_hour,Flight2.arrive_min,0))
            )
            AS arrive_time,
            ( -- 總票價
                ROUND((Flight1.price + Flight2.price)*0.9) -- 打九折之後四捨五入
            )
            AS total_price,
            ( -- 轉機時間1~2
                
                TIMEDIFF(
                    -- 班機二出發時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight2.departure_date , MAKETIME(Flight2.depart_hour,Flight2.depart_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight2.departure -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                ,
                    -- 班機一抵達時間
                    SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                        ADDTIME(Flight1.arrival_date , MAKETIME(Flight1.arrive_hour,Flight1.arrive_min,0)  -- 組合真正的出發日期時間
                        )
                        , 
                        SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                            (
                                SELECT time_zone FROM Airport WHERE Airport.name = Flight1.destination -- 取的位移後的時區
                            )
                            ,'12:00:00' -- 逆位移12小時
                        )
                    )
                )
            )
            AS time_1,
            null
            AS time_2
            FROM Flight AS Flight1 JOIN Flight AS Flight2 
            WHERE Flight1.departure = ?  -- 出發地點
            AND Flight1.destination = Flight2.departure -- 1&2間中繼站必須相等
            AND Flight2.destination = ?  -- 抵達地點
        ) AS MatchFlight
        WHERE MatchFlight.time_1 >= '0000-00-00 02:00:00' -- 檢查轉機時間是否足夠
        AND MatchFlight.time_1 <= '0000-00-00 12:00:00' 
    )
) 
AS TransferTable
ORDER BY 
DOC_SQL;

    $transfer_sql_night[0] = <<<DOC_SQL
SELECT id1,id2,id3,f_time1,f_time2,f_time3,ADDTIME(f_time1,ADDTIME(f_time2,f_time3))  AS flight_time,transfer_time,(ADDTIME(transfer_time,ADDTIME(f_time1,ADDTIME(f_time2,f_time3)))) AS total_time,total_price,depart_time,arrive_time FROM 
( 
    (SELECT id AS id1,null AS id2,null AS id3,
        (
            TIMEDIFF( -- 直航
                -- 抵達時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(arrival_date , MAKETIME(arrive_hour,arrive_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.destination -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
                ,
                -- 出發時間(UTC)
                SUBTIME( -- SUB：一律轉換為UTC時間，亦即扣掉自己的時區偏移
                    ADDTIME(departure_date , MAKETIME(depart_hour,depart_min,0)  -- 組合真正的出發日期時間
                    )
                    , 
                    SUBTIME( -- 資料庫中的時區為位移12小時之後的，因此要減回來
                        (
                            SELECT time_zone FROM Airport WHERE Airport.name = Flight.departure -- 取的位移後的時區
                        )
                        ,'12:00:00' -- 逆位移12小時
                    )
                )
            )
        )
        AS f_time1,
        '00:00:00' AS f_time2,
        '00:00:00' AS f_time3,
        (
            ADDTIME(Flight.departure_date , MAKETIME(Flight.depart_hour,Flight.depart_min,0))
        )
        AS depart_time,
        (
            ADDTIME(Flight.arrival_date , MAKETIME(Flight.arrive_hour,Flight.arrive_min,0))
        )
        AS arrive_time,
        '00:00:00' AS transfer_time,
        
        
        ( -- 總票價
            price
        )
        AS total_price 
        FROM Flight
        WHERE departure = ? AND destination = ?
    ) 
    
) 
AS TransferTable
ORDER BY 
DOC_SQL;
    $default_order = "";


?>