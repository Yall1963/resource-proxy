﻿using System.Collections.Generic;
using System.Threading.Tasks;
using System.Net;
using System.Web;
using System.Web.Mvc;
using FP.Cloud.OnlineRateTable.Common.RateTable;
using FP.Cloud.OnlineRateTable.Web.Repositories;
using FP.Cloud.OnlineRateTable.Web.Models.ViewModels;
using FP.Cloud.OnlineRateTable.Web.Scenarios;
using FP.Cloud.OnlineRateTable.Common.ScenarioRunner;
using Ninject;

namespace FP.Cloud.OnlineRateTable.Web.Controllers
{
    [Authorize(Roles = "RateTableAdministrators")]
    public class RateTableController : BaseController
    {
        #region members
        private RateTableRepository m_Repository;
        #endregion

        #region properties
        [Inject]
        public ExtractArchiveScenario ExtractScenario{get; set;}
        [Inject]
        public ReadMetaDataScenario ReadMetaScenario { get; set; }
        #endregion

        #region constructor
        public RateTableController(RateTableRepository repository)
        {
            m_Repository = repository;
        }
        #endregion
        // GET: RateTable
        public async Task<ActionResult> Index()
        {
            ApiResponse<List<RateTableInfo>> response = await m_Repository.GetAllRateTables(GetAuthToken());
            return HandleApiResponse(response, View(response.ApiResult), HttpNotFound());
        }

        // GET: RateTable/Details/5
        public async Task<ActionResult> Details(int? id)
        {
            if (id.HasValue == false)
            {
                return new HttpStatusCodeResult(HttpStatusCode.BadRequest);
            }
            ApiResponse<RateTableInfo> response = await m_Repository.GetById(id.Value, GetAuthToken());
            return HandleApiResponse(response, View(response.ApiResult), HttpNotFound());
        }

        // GET: RateTable/Create
        public ActionResult Create()
        {
            return View();
        }

        // POST: RateTable/Create
        // To protect from overposting attacks, please enable the specific properties you want to bind to, for 
        // more details see http://go.microsoft.com/fwlink/?LinkId=317598.
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<ActionResult> Create([Bind(Include = "ValidFrom,Culture,ZipUpload")] RateTableViewModel rateTableViewModel, HttpPostedFileBase upload)
        {
            if (upload == null || upload.ContentLength == 0)
            {
                ModelState.AddModelError("ZipUpload", "This field is required");
            }
            else if (upload.ContentType != "application/x-zip-compressed")
            {
                ModelState.AddModelError("ZipUpload", "Please choose a zip archive.");
            }
            if (ModelState.IsValid)
            {
                //add basic stuff
                RateTableInfo rateTableInfo = new RateTableInfo();
                rateTableInfo.Culture = rateTableViewModel.Culture;
                rateTableInfo.ValidFrom = rateTableViewModel.ValidFrom;

                //add the files
                ScenarioResult<List<RateTableFileInfo>> extractResult = ExtractScenario.Execute(upload.InputStream);
                if(extractResult.Success)
                {
                    rateTableInfo.PackageFiles = extractResult.Value;
                    ScenarioResult metaResult = ReadMetaScenario.Execute(rateTableInfo);
                    if(metaResult.Success)
                    {
                        //parsing meta file succeeded - information is stored in rateTableInfo
                        //add new item to database
                        ApiResponse<RateTableInfo> response = await m_Repository.AddNewRateTable(rateTableInfo, GetAuthToken());
                        return HandleApiResponse(response, RedirectToAction("Index"), new HttpStatusCodeResult(HttpStatusCode.BadRequest));
                    }
                }
                ModelState.AddModelError("ZipUpload", "Error processing RateTable file");
            }

            return View(rateTableViewModel);
        }

        // GET: RateTable/Edit/5
        public async Task<ActionResult> Edit(int? id)
        {
            if (id.HasValue == false)
            {
                return new HttpStatusCodeResult(HttpStatusCode.BadRequest);
            }
            ApiResponse<RateTableInfo> response = await m_Repository.GetById(id.Value, GetAuthToken());
            return HandleApiResponse(response, View(response.ApiResult), HttpNotFound());
        }

        // POST: RateTable/Edit/5
        // To protect from overposting attacks, please enable the specific properties you want to bind to, for 
        // more details see http://go.microsoft.com/fwlink/?LinkId=317598.
        [HttpPost]
        [ValidateAntiForgeryToken]
        public async Task<ActionResult> Edit([Bind(Include = "Id,Variant,VersionNumber,CarrierId,CarrierDetails,ValidFrom,Culture")] RateTableInfo rateTableInfo)
        {
            if (ModelState.IsValid)
            {
                ApiResponse<RateTableInfo> response = await m_Repository.UpdateRateTable(rateTableInfo, GetAuthToken());
                return HandleApiResponse(response, RedirectToAction("Index"), new HttpStatusCodeResult(HttpStatusCode.BadRequest));
            }
            return View(rateTableInfo);
        }

        // GET: RateTable/Delete/5
        public async Task<ActionResult> Delete(int? id)
        {
            if (id.HasValue == false)
            {
                return new HttpStatusCodeResult(HttpStatusCode.BadRequest);
            }
            ApiResponse<RateTableInfo> response = await m_Repository.GetById(id.Value, GetAuthToken());
            return HandleApiResponse(response, View(response.ApiResult), HttpNotFound());
        }

        // POST: RateTable/Delete/5
        [HttpPost, ActionName("Delete")]
        [ValidateAntiForgeryToken]
        public async Task<ActionResult> DeleteConfirmed(int id)
        {
            ApiResponse<RateTableInfo> response = await m_Repository.GetById(id, GetAuthToken());
            if (response.ApiResult == null)
            {
                return HttpNotFound();
            }
            ApiResponse<bool> deleteResponse = await m_Repository.DeleteRateTable(id, GetAuthToken());
            return HandleApiResponse(response, RedirectToAction("Index"), new HttpStatusCodeResult(HttpStatusCode.BadRequest));
        }

        #region protected
        protected override void Dispose(bool disposing)
        {
            if (disposing)
            {
                m_Repository.Dispose();
            }
            base.Dispose(disposing);
        }
        #endregion
    }
}
