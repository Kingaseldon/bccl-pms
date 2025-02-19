BEGIN
  DECLARE employeeloopcomplete INTEGER DEFAULT 0;
  DECLARE VEmployeeId INT;
  DECLARE VNewScore DECIMAL(6,2);
  DECLARE VNewOutOf DECIMAL(6,2);
  DECLARE VEmployeeScore DECIMAL(6,2);
  DECLARE VEmployeeOutOf DECIMAL(6,2);
  DECLARE VEmployeeSubmissionId CHAR(36);
  DECLARE VMarkToBeAdjusted DECIMAL(12,2);
  DECLARE VLevel2CriteriaType INT;
  DECLARE CursorEmployees CURSOR FOR SELECT Id from mas_employee where coalesce(Status,0) = 1;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET employeeloopcomplete = 1;

  SET VMarkToBeAdjusted = PAdjustmentMarks;

  DELETE FROM pms_submissionfinalscore WHERE SubmissionId in (select Id from pms_submission WHERE (DATE_FORMAT(SubmissionTime,'%Y-%m-%d') >= PFromDate and DATE_FORMAT(SubmissionTime,'%Y-%m-%d') <= PToDate));

  OPEN CursorEmployees;

    EmployeeLoop: LOOP

      FETCH CursorEmployees INTO VEmployeeId;
      IF employeeloopcomplete = 1 THEN
        LEAVE EmployeeLoop;
      END IF;

      SET VLevel2CriteriaType = (select T2.Level2CriteriaType from pms_submission T2 join mas_employee T3 on T3.Id = T2.EmployeeId where (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= PFromDate and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= PToDate) and T2.EmployeeId = VEmployeeId);

      IF VLevel2CriteriaType = 2 THEN
      	SET VEmployeeScore = (select sum(T1.Level2Rating) as QuantitativeScoreTotal from pms_submissiondetail T1 join pms_submission T2 on T2.Id = T1.SubmissionId join mas_employee T3 on T3.Id = T2.EmployeeId where (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= PFromDate and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= PToDate) and T1.ApplicableToLevel2 = 0 and T2.EmployeeId = VEmployeeId);
      ELSE
      	SET VEmployeeScore = (select sum(T1.Level1Rating) as QuantitativeScoreTotal from pms_submissiondetail T1 join pms_submission T2 on T2.Id = T1.SubmissionId join mas_employee T3 on T3.Id = T2.EmployeeId where (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= PFromDate and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= PToDate) and T1.ApplicableToLevel2 = 0 and T2.EmployeeId = VEmployeeId);
      END IF;


      	SET VEmployeeOutOf = (select sum(T1.Weightage) as QuantitativeScoreTotal from pms_submissiondetail T1 join pms_submission T2 on T2.Id = T1.SubmissionId join mas_employee T3 on T3.Id = T2.EmployeeId where (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= PFromDate and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= PToDate) and T1.ApplicableToLevel2 = 0 and T2.EmployeeId = VEmployeeId);

      SET VEmployeeSubmissionId = (select T2.Id from pms_submission T2 join mas_employee T3 on T3.Id = T2.EmployeeId where (DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') >= PFromDate and DATE_FORMAT(T2.SubmissionTime,'%Y-%m-%d') <= PToDate) and T2.EmployeeId = VEmployeeId);
      IF VEmployeeSubmissionId is not null THEN
        IF VEmployeeScore > 0 THEN
          SET VNewOutOf = VEmployeeOutOf - PAdjustmentPercent;
          SET VNewScore = ((VEmployeeScore/VEmployeeOutOf) * VNewOutOf) + VMarkToBeAdjusted;
          IF VNewScore > 0 THEN
            INSERT INTO pms_submissionfinalscore (Id,SubmissionId,FinalAdjustmentPercent,FinalScore,created_at)
            VALUES (UUID(),VEmployeeSubmissionId,PAdjustmentPercent,VNewScore,NOW());
          END IF;
        END IF;
      END IF;

    END LOOP EmployeeLoop;

  CLOSE CursorEmployees;
END