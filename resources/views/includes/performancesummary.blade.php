<?php $appraisalType = ''; ?>
@if ((bool) $application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0)
    @if ($application[0]->Level2CriteriaType == 2)
        <?php $appraisalType = 1; ?>
    @else
        <?php $appraisalType = 2; ?>
    @endif
@else
    <?php $appraisalType = 3; ?>
@endif

<?php
$controllerObject = new App\Http\Controllers\Controller();
$finalAdjustmentPercentDetails = $controllerObject->fetchCurrentPMSAdjustmentDetails($application[0]->Id);
?>
<input type="hidden" name="submissionId" id="submissionId" value="{{ request()->id }}" />
<div class="table-responsive">
    <table class="table table-bordered table-condensed less-padding" id="calc-total">
        <thead>
            <tr>
                <th style="width:20px;">Sl #</th>
                <th>Assessment Area</th>
                <th class="text-right" style="width:20px;">Weight (%)</th>
                <th class="text-right" style="width:20px;">Self Rating</th>
                {{-- START HERE 1 --}}
                @if (!($application[0]->LastStatusId == CONST_PMSSTATUS_DRAFT && $application[0]->ReportingLevel1EmployeeId))
                    @if ($level1AppraiserCount > 1)
                        @for ($i = 1; $i <= $level1AppraiserCount; $i++)
                            <?php $var = 'total' . $i;
                            $$var = 0; ?>
                        @endfor
                        @foreach ($level1Appraisers as $level1Appraiser)
                            <th class="text-right">Level 1 Rating <br> ({{ $level1Appraiser->Name }})</th>
                        @endforeach
                    @endif
                @endif
                @if (!($application[0]->LastStatusId == CONST_PMSSTATUS_DRAFT && $application[0]->ReportingLevel2EmployeeId))
                    <th class="text-right" style="">
                        @if ($level1AppraiserCount > 1)
                            Avg.
                            @endif Level 1 Rating @if ($level1AppraiserCount == 1)
                                <br> ({{ $application[0]->Level1Employee }})
                            @endif
                    </th>
                    @if ((bool) $application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0)
                        @if ($level2AppraiserCount > 1)
                            @for ($i = 1; $i <= $level2AppraiserCount; $i++)
                                <?php $var = 'total2' . $i;
                                $$var = 0; ?>
                            @endfor
                            @foreach ($level2Appraisers as $level2Appraiser)
                                <th class="text-right">Level 2 Rating <br>({{ $level2Appraiser->Name }})</th>
                            @endforeach
                        @endif
                        <th class="text-right" style="">
                            @if ($level2AppraiserCount > 1)
                                Avg.
                                @endif Level 2 Rating @if ($level2AppraiserCount == 1)
                                    <br> ({{ $application[0]->Level2Employee }})
                                @endif
                        </th>
                    @endif
                @endif
                {{-- END HERE 1 --}}
            </tr>
        </thead>
        <tbody>
            <?php $count = 1;
            $totalWeightage = $level1WeightedTotal = $level2WeightedTotal = $selfRatingTotal = $level1QualitativeTotal = $level1QuantitativeTotal = $level2QualitativeTotal = $level2QuantitativeTotal = $level1RatingTotal = $level2RatingTotal = $qualitativeWeightageTotal = $quantitativeWeightageTotal = 0; ?>
            @foreach ($applicationDetails as $assessmentArea)
                <?php $randomKey = randomString(); ?>
                <tr>
                    <td>{{ $count }}.</td>
                    <td class="description">
                        {{ $assessmentArea->AssessmentArea }}
                    </td>
                    <td class="text-right">
                        {{ $assessmentArea->Weightage }}
                        <?php $totalWeightage += $assessmentArea->Weightage; ?>
                    </td>
                    <td class="text-right">
                        {{ $assessmentArea->SelfRating }} <?php $selfRatingTotal += $assessmentArea->SelfRating; ?>
                    </td>
                    {{-- START HERE 2 --}}
                    @if (!($application[0]->LastStatusId == CONST_PMSSTATUS_DRAFT && $application[0]->ReportingLevel1EmployeeId))
                        @if ($level1AppraiserCount > 1)
                            <?php $innerCount = 1; ?>
                            @foreach ($level1Appraisers as $level1Appraiser)
                                <?php $var = 'total' . $innerCount;
                                $$var += isset($level1Multiple[$level1Appraiser->ReportingLevel1EmployeeId][$assessmentArea->Id]) ? $level1Multiple[$level1Appraiser->ReportingLevel1EmployeeId][$assessmentArea->Id] : 0; ?>
                                <td class="text-right">
                                    {{ $level1Multiple[$level1Appraiser->ReportingLevel1EmployeeId][$assessmentArea->Id] ?? '' }}
                                </td>
                                <?php $innerCount++; ?>
                            @endforeach
                        @endif
                    @endif
                    @if (!($application[0]->LastStatusId == CONST_PMSSTATUS_DRAFT && $application[0]->ReportingLevel2EmployeeId))
                        <td class="text-right">
                            @if ($isAdmin == false)
                                {{ $assessmentArea->Level1Rating }}
                            @else
                                @if ($assessmentArea->Level1Rating > 0)
                                    <input type="number" class="editable-score"
                                        name="Level1Rating[{{ $assessmentArea->Id }}]"
                                        value="{{ $assessmentArea->Level1Rating }}"
                                        data-id="{{ $assessmentArea->Id }}" data-type="Level1Rating" min="0"
                                        max="{{ $assessmentArea->Weightage }}"
                                        @if ($isAdmin == false) readonly disabled @endif step="any"
                                        style="width:20%;" />
                                @endif
                            @endif
                        </td>
                        @if ($assessmentArea->ApplicableToLevel2 == 0)
                            <?php $quantitativeWeightageTotal += $assessmentArea->Weightage; ?>
                            <?php $level1QuantitativeTotal += $assessmentArea->Level1Rating; ?>
                        @else
                            <?php $qualitativeWeightageTotal += $assessmentArea->Weightage; ?>
                            <?php $level1QualitativeTotal += $assessmentArea->Level1Rating; ?>
                        @endif

                        @if ((bool) $application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0)
                            @if ($level2AppraiserCount > 1)
                                <?php $innerCount = 1; ?>
                                @foreach ($level2Appraisers as $level2Appraiser)
                                    <?php $var = 'total2' . $innerCount;
                                    $$var += isset($level2Multiple[$level2Appraiser->ReportingLevel2EmployeeId][$assessmentArea->Id]) ? $level2Multiple[$level2Appraiser->ReportingLevel2EmployeeId][$assessmentArea->Id] : 0; ?>
                                    <td class="text-right">
                                        {{ $level2Multiple[$level2Appraiser->ReportingLevel2EmployeeId][$assessmentArea->Id] ?? '' }}
                                    </td>
                                    <?php $innerCount++; ?>
                                @endforeach
                            @endif

                            <td class="text-right">
                                @if ($isAdmin == false)
                                    {{ $assessmentArea->Level2Rating }}
                                @else
                                    @if ($assessmentArea->Level2Rating > 0)
                                        <input type="number" class="editable-score"
                                            name="Level2Rating[{{ $assessmentArea->Id }}]"
                                            value="{{ $assessmentArea->Level2Rating }}"
                                            data-id="{{ $assessmentArea->Id }}" data-type="Level2Rating" min="0"
                                            max="{{ $assessmentArea->Weightage }}" step="any"
                                            style="width: 20%;" />
                                    @endif
                                @endif
                                @if ($assessmentArea->ApplicableToLevel2 == 0)
                                    <?php $level2QuantitativeTotal += $assessmentArea->Level2Rating; ?>
                                @else
                                    <?php $level2QualitativeTotal += $assessmentArea->Level2Rating; ?>
                                @endif
                            </td>
                        @endif
                    @endif
                    {{-- END HERE 2 --}}
                </tr>
                <?php $count++; ?>
            @endforeach

            <tr>
                <td colspan="2" class="text-right"><strong>Total</strong></td>
                <td class="text-right">
                    {{ number_format($totalWeightage, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($selfRatingTotal, 2) }}
                </td>
                {{-- START HERE 3 --}}
                @if (!($application[0]->LastStatusId == CONST_PMSSTATUS_DRAFT && $application[0]->ReportingLevel1EmployeeId))
                    @if ($level1AppraiserCount > 1)
                        @for ($i = 1; $i <= $level1AppraiserCount; $i++)
                            <?php $var = 'total' . $i; ?>
                            <td class="text-right"; ?>
                                {{ number_format($$var, 2) }}
                            </td>
                        @endfor
                    @endif
                @endif
                @if (!($application[0]->LastStatusId == CONST_PMSSTATUS_DRAFT && $application[0]->ReportingLevel2EmployeeId))
                    <td class="text-right">
                        <?php $level1RatingTotal = $level1QualitativeTotal + $level1QuantitativeTotal; ?>
                        {{ number_format($level1RatingTotal, 2) }}
                    </td>
                    @if ((bool) $application[0]->WeightageForLevel2 && $application[0]->WeightageForLevel2 > 0)
                        @if ($level2AppraiserCount > 1)
                            @for ($i = 1; $i <= $level2AppraiserCount; $i++)
                                <?php $var = 'total2' . $i; ?>
                                <td class="text-right"; ?>
                                    {{ number_format($$var, 2) }}
                                </td>
                            @endfor
                        @endif
                        <td class="text-right">
                            <?php $level2RatingTotal = $level2QualitativeTotal + $level2QuantitativeTotal; ?>
                            {{ number_format($level2RatingTotal, 2) }}
                        </td>
                    @endif
                @endif
                {{-- END HERE 3 --}}
            </tr>
        </tbody>
    </table>
</div>

@if (
    $application[0]->LastStatusId == CONST_PMSSTATUS_APPROVED ||
        $application[0]->LastStatusId == CONST_PMSSTATUS_VERIFIED)
    <h5>Performance Summary</h5>
    <div class="row">
        <div class="col-md-9">
            <div class="table-responsive">
                <table class="table table-condensed table-bordered less-padding">
                    <thead>
                        <tr>
                            <th>Appraisal by</th>
                            <th class="text-right">Score</th>
                            <th class="text-right">Weight (%)</th>
                            <th class="text-right">Weighted Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($appraisalType == 1)
                            <tr>
                                <td>
                                    {{ $application[0]->Level1Employee }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($level1RatingTotal, 2) }}
                                    @if ((bool) $finalAdjustmentPercentDetails)
                                        <?php $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal) * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment']) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal; ?>
                                        <strong style="font-size:12px;">(Adjusted:
                                            {{ number_format($adjustedLevel1Score, 2) }})</strong>
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{ $application[0]->WeightageForLevel1 }}
                                </td>
                                <td class="text-right">
                                    <?php
                                    // $level1WeightedTotal = ($level1RatingTotal / 100) * $application[0]->WeightageForLevel1;
                                    $level1WeightedTotal = ($level1RatingTotal / $quantitativeWeightageTotal) * $application[0]->WeightageForLevel1;
                                    ?>
                                    {{ number_format($level1WeightedTotal, 2) }}
                                    @if ((bool) $finalAdjustmentPercentDetails)
                                        <?php
                                        // $level1AdjustedTotal = (round($adjustedLevel1Score,2) / 100) * $application[0]->WeightageForLevel1;
                                        $level1AdjustedTotal = (round($adjustedLevel1Score, 2) / $quantitativeWeightageTotal) * $application[0]->WeightageForLevel1;
                                        ?>
                                        <strong style="font-size:12px">(Adjusted:
                                            {{ number_format($level1AdjustedTotal, 2) }})</strong>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {{ $application[0]->Level2Employee }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($level2RatingTotal, 2) }}
                                    @if ((bool) $finalAdjustmentPercentDetails)
                                        <?php $adjustedLevel2Score = ($level2QualitativeTotal / $qualitativeWeightageTotal) * ($qualitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment']) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level2QuantitativeTotal; ?>
                                        <strong style="font-size:12px">(Adjusted:
                                            {{ number_format($adjustedLevel2Score, 2) }})</strong>
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{ $application[0]->WeightageForLevel2 }}
                                </td>
                                <td class="text-right">
                                    <?php
                                    // $level2WeightedTotal = ($level2RatingTotal / 100) * $application[0]->WeightageForLevel2;
                                    $level2WeightedTotal = ($level2RatingTotal / $qualitativeWeightageTotal) * $application[0]->WeightageForLevel2;
                                    ?>
                                    {{ number_format($level2WeightedTotal, 2) }}
                                    @if ((bool) $finalAdjustmentPercentDetails)
                                        <?php
                                        // $level2AdjustedTotal = (round($adjustedLevel2Score,2) / 100) * $application[0]->WeightageForLevel2;
                                        $level2AdjustedTotal = (round($adjustedLevel2Score, 2) / $qualitativeWeightageTotal) * $application[0]->WeightageForLevel2;
                                        ?>
                                        <strong style="font-size:12px">(Adjusted:
                                            {{ number_format($level2AdjustedTotal, 2) }})</strong>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><strong>Normalized Score:</strong></td>
                                <td class="text-right">
                                    <strong>{{ number_format(round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2), 2) }}
                                        @if ((bool) $finalAdjustmentPercentDetails)
                                            <strong style="font-size: 10pt;">(Adjusted:
                                                {{ number_format(round($level1AdjustedTotal, 2) + round($level2AdjustedTotal, 2), 2) }})</strong>
                                        @endif
                                    </strong>
                                </td>
                                <input type="hidden" name="FinalScore" class="final-score"
                                    value="{{ round((bool) $finalAdjustmentPercentDetails ? round($level1AdjustedTotal, 2) + round($level2AdjustedTotal, 2) : round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2), 2) }}" />
                            </tr>
                        @elseif($appraisalType == 2)
                            <tr>
                                <td>
                                    {{ $application[0]->Level1Employee }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($level1RatingTotal, 2) }}
                                    @if ((bool) $finalAdjustmentPercentDetails)
                                        <?php $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal) * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment']) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal; ?>
                                        <strong style="font-size:12px;">(Adjusted:
                                            {{ number_format($adjustedLevel1Score, 2) }})</strong>
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{ $application[0]->WeightageForLevel1 }}
                                </td>
                                <td class="text-right">
                                    <?php
                                    // $level1WeightedTotal = ($level1RatingTotal / 100) * $application[0]->WeightageForLevel1;
                                    $level1WeightedTotal = ($level1RatingTotal / $quantitativeWeightageTotal) * $application[0]->WeightageForLevel1;
                                    ?>
                                    {{ number_format($level1WeightedTotal, 2) }}
                                    @if ((bool) $finalAdjustmentPercentDetails)
                                        <?php
                                        // $level1AdjustedTotal = (round($adjustedLevel1Score,2) / 100) * $application[0]->WeightageForLevel1;
                                        $level1AdjustedTotal = (round($adjustedLevel1Score, 2) / $quantitativeWeightageTotal) * $application[0]->WeightageForLevel1;
                                        ?>
                                        <strong style="font-size:12px">(Adjusted:
                                            {{ number_format($level1AdjustedTotal, 2) }})</strong>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {{ $application[0]->Level2Employee }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($level2RatingTotal, 2) }}

                                </td>
                                <td class="text-right">
                                    {{ $application[0]->WeightageForLevel2 }}
                                </td>
                                <td class="text-right">
                                    <?php $level2WeightedTotal = ($level2RatingTotal / $qualitativeWeightageTotal) * $application[0]->WeightageForLevel2; ?>
                                    {{ number_format($level2WeightedTotal, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><strong>Normalized Score:</strong></td>
                                <td class="text-right">
                                    <strong>{{ number_format(round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2), 2) }}
                                        @if ((bool) $finalAdjustmentPercentDetails)
                                            <strong style="font-size: 10pt;">(Adjusted:
                                                {{ number_format(round($level1AdjustedTotal, 2) + round($level2WeightedTotal, 2), 2) }})</strong>
                                        @endif
                                    </strong>
                                </td>
                                <input type="hidden" name="FinalScore" class="final-score"
                                    value="{{ round((bool) $finalAdjustmentPercentDetails ? round($level1AdjustedTotal, 2) + round($level2WeightedTotal, 2) : round($level1WeightedTotal, 2) + round($level2WeightedTotal, 2), 2) }}" />
                            </tr>
                        @else
                            <tr>
                                <td>
                                    {{ $application[0]->Level1Employee }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($level1RatingTotal, 2) }}
                                    @if ((bool) $finalAdjustmentPercentDetails)
                                        <?php $adjustedLevel1Score = ($level1QuantitativeTotal / $quantitativeWeightageTotal) * ($quantitativeWeightageTotal - $finalAdjustmentPercentDetails['Adjustment']) + $finalAdjustmentPercentDetails['ScoreToInject'] + $level1QualitativeTotal; ?>
                                        <strong style="font-size:12px;">(Adjusted:
                                            {{ number_format($adjustedLevel1Score, 2) }})</strong>
                                    @endif
                                </td>
                                <td class="text-right">
                                    {{ $application[0]->WeightageForLevel1 }}
                                </td>
                                <td class="text-right">
                                    <?php
                                    $level1WeightedTotal = ($level1RatingTotal / 100) * $application[0]->WeightageForLevel1;
                                    // $level1WeightedTotal = ($level1RatingTotal / $quantitativeWeightageTotal) * $application[0]->WeightageForLevel1;
                                    ?>
                                    {{ number_format($level1WeightedTotal, 2) }}
                                    @if ((bool) $finalAdjustmentPercentDetails)
                                        <?php
                                        // $level1AdjustedTotal = (round($adjustedLevel1Score,2) / 100) * $application[0]->WeightageForLevel1;
                                        $level1AdjustedTotal = (round($adjustedLevel1Score, 2) / $quantitativeWeightageTotal) * $application[0]->WeightageForLevel1;
                                        ?>
                                        <strong style="font-size:12px">(Adjusted:
                                            {{ number_format($level1AdjustedTotal, 2) }})</strong>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><strong>Normalized Score:</strong></td>
                                <td class="text-right"><strong>{{ number_format($level1WeightedTotal, 2) }} @if ((bool) $finalAdjustmentPercentDetails)
                                            <strong style="font-size: 10pt;">(Adjusted:
                                                {{ number_format($level1AdjustedTotal, 2) }})</strong>
                                        @endif
                                    </strong></td>
                                <input type="hidden" name="FinalScore" class="final-score"
                                    value="{{ round((bool) $finalAdjustmentPercentDetails ? round($level1AdjustedTotal, 2) : round($level1WeightedTotal, 2), 2) }}" />
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (isset($application[0]->Outcome) && (bool) $application[0]->Outcome)
        @if (
            $application[0]->PMSOutcomeId == CONST_PMSOUTCOME_NOACTION ||
                ($application[0]->PMSOutcomeId != CONST_PMSOUTCOME_NOACTION && $application[0]->OfficeOrderEmailed == 1))
            <h5>Result</h5>
            <strong>{{ $application[0]->OutcomeDateTime }}: </strong>{{ $application[0]->Outcome }}
            <br><br>
        @endif
    @endif

    {!! (bool) $application[0]->FinalRemarks
        ? '<h5>Remarks</h5>' . $application[0]->FinalRemarks . '<br/><br/>'
        : '' !!}
    @if ($application[0]->EmployeeId == Auth::user()->Id)
        @if (isset($type) && $type == 3)
            <?php $url = 'pmshistory'; ?>
        @else
            <?php $url = 'trackpms'; ?>
        @endif

        <div class="row">
            <div class="col-md-4">
                <a href="{{ url($url) }}" style="" class="btn btn-primary"><i class="fa fa-backward"></i>
                    Back</a>
            </div>
        </div>
    @endif
@endif
