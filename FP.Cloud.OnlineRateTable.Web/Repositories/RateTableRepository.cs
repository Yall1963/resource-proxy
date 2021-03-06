﻿using FP.Cloud.OnlineRateTable.Common.RateTable;
using RestSharp;
using System;
using System.Collections.Generic;
using System.Configuration;
using System.Threading.Tasks;

namespace FP.Cloud.OnlineRateTable.Web.Repositories
{
    public class RateTableRepository : RateTableApi, IDisposable
    {
        #region members
        private string m_RateTableApi = string.Format("{0}/api/RateTableAdministration", ConfigurationManager.AppSettings["RateTableUrl"]);
        #endregion
        #region public
        public async Task<ApiResponse<List<RateTableInfo>>> GetAllRateTables(string authToken)
        {
            RestRequest request = GetNewRequest();
            request.Resource = "GetAll";
            request.AddParameter("includeFileData", false, ParameterType.GetOrPost);

            return await Execute<List<RateTableInfo>>(request, m_RateTableApi, authToken) ;
        }

        public async Task<ApiResponse<RateTableInfo>> GetById(int id, string authToken)
        {
            RestRequest request = GetNewRequest();
            request.Resource = "GetById";
            request.AddParameter("id", id, ParameterType.GetOrPost);

            return await Execute<RateTableInfo>(request, m_RateTableApi, authToken);
        }

        public async Task<ApiResponse<List<RateTableInfo>>> GetFilteredRateTables(string authToken)
        {
            return null;
        }

        public async Task<ApiResponse<RateTableInfo>> AddNewRateTable(RateTableInfo info, string authToken)
        {
            info.ValidFrom = new DateTime(info.ValidFrom.Year, info.ValidFrom.Month, info.ValidFrom.Day, 0, 0, 0, DateTimeKind.Utc);

            RestRequest request = GetNewRequest();
            request.Resource = "AddNew";
            request.Method = Method.POST;
            request.AddBody(info);

            return await Execute<RateTableInfo>(request, m_RateTableApi, authToken);
        }

        public async Task<ApiResponse<RateTableInfo>> UpdateRateTable(RateTableInfo info, string authToken)
        {
            info.ValidFrom = new DateTime(info.ValidFrom.Year, info.ValidFrom.Month, info.ValidFrom.Day, 0, 0, 0, DateTimeKind.Utc);

            RestRequest request = GetNewRequest();
            request.Resource = "Update";
            request.Method = Method.POST;
            request.AddBody(info);

            return await Execute<RateTableInfo>(request, m_RateTableApi, authToken);
        }

        public async Task<ApiResponse<bool>> DeleteRateTable(int id, string authToken)
        {
            RestRequest request = GetNewRequest();
            request.Resource = @"Delete/{id}";
            request.Method = Method.DELETE;
            request.AddParameter("id", id, ParameterType.UrlSegment);

            return await Execute<bool>(request, m_RateTableApi, authToken);
        }

        #region IDisposable Support
        private bool m_DisposedValue = false; // To detect redundant calls

        protected virtual void Dispose(bool disposing)
        {
            if (!m_DisposedValue)
            {
                if (disposing)
                {
                    // TODO: dispose managed state (managed objects).
                }

                // TODO: free unmanaged resources (unmanaged objects) and override a finalizer below.
                // TODO: set large fields to null.

                m_DisposedValue = true;
            }
        }

        // TODO: override a finalizer only if Dispose(bool disposing) above has code to free unmanaged resources.
        // ~RateTableRepository() {
        //   // Do not change this code. Put cleanup code in Dispose(bool disposing) above.
        //   Dispose(false);
        // }

        // This code added to correctly implement the disposable pattern.
        public void Dispose()
        {
            // Do not change this code. Put cleanup code in Dispose(bool disposing) above.
            Dispose(true);
            // TODO: uncomment the following line if the finalizer is overridden above.
            // GC.SuppressFinalize(this);
        }
        #endregion
        #endregion
    }
}